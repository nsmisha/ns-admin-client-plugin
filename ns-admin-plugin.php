<?php
/*
		Plugin Name: NS Admin Plugin
		Plugin URI: http://neversettle.it
		Description: admin dashbord plugin
		Text Domain: ns-admin-plugin
		Author: Never Settle
		Version: 1.0.1
		Author URI: http://neversettle.it
		License: GPLv2 or later
	*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly!
}

require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );
require_once( plugin_dir_path( __FILE__ ) . 'console-log.php' );
//TODO Class autoloder
require_once( plugin_dir_path( __FILE__ ) . 'lib' . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'class-ns-client-task-settings.php' );

/*
 * Check version
 */
require_once( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php' );
// TODO: rename this class
class NS_Admin_Plugin {
	
	private $path;                // path to plugin dir
	private $wp_plugin_page;    // url to plugin page on wp.org
	private $ns_plugin_page;    // url to pro plugin page on ns.it
	private $ns_admin_plugin;    // friendly name of this plugin for re-use throughout
	private $ns_plugin_menu;    // friendly menu title for re-use throughout
	private $ns_plugin_slug;    // slug name of this plugin for re-use throughout
	private $ns_plugin_ref;    // reference name of the plugin for re-use throughout
	private $post_data;
	private $multipart_boundary = 'bWH4JVmYCnf6GfXacres';
	private $plugin_info;
	private $plugin_site_info;
	protected $error;
	protected $settings;
	const clientId = '1079587052757-lg5mlbjgnn04m8dsuth30gcbousaak9f.apps.googleusercontent.com';
	const clientSecret = 'KO-Wf2to6HFOcBLrvgLR8SZc';
	const redirect = 'urn:ietf:wg:oauth:2.0:oob';
	public $client;
	private $analytics;
	private $gareports;
	private $app_token;
	private $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/user-name/repo-name/',
		__FILE__,
		$ns_plugin_slug
	);
	/**
	 * @var NS_Client_Task_Settings
	 */
	private $task_settings;
	private $task_type = 'ns_tasks';
	private $per_page = 10;
	
	const MANAGER_SETTINGS_KEY = 'ns_admin_manager_settings';
	
	/**
	 * NS_Admin_Plugin constructor.
	 *
	 * @param NS_Client_Task_Settings $task_settings
	 */
	public function __construct( NS_Client_Task_Settings $task_settings ) {
		$this->init_plugin_settings();
		
		$this->task_settings = $task_settings;

//        if ( !empty( $this->app_token = get_site_option('gapi_access_token') ) ) {
		$this->init_google_client();
//        }
		
		add_action( 'wp_ajax_nsc_veryfy_connection_to_remote_site', array(
			$this,
			'ajax_verify_connection_to_remote_site'
		) );
		add_action( 'wp_ajax_nopriv_ns_verify_connection_to_remote_site', array(
			$this,
			'respond_to_verify_connection_to_remote_site'
		) );
		add_action( 'wp_ajax_nopriv_respond_to_verify_generate_new_url', array(
			$this,
			'respond_to_verify_generate_new_url'
		) );
		
		add_action( 'wp_ajax_remove_connection_data', array( $this, 'remove_connection_data' ) );
		
		add_action( 'plugins_loaded', array( $this, 'setup_plugin' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 11 );
		add_action( 'network_admin_notices', array( $this, 'admin_notices' ), 11 );
		
		//Create custom ns wp admin dashboard
		add_action( 'admin_menu', array( $this, 'ns_register_menu' ) );
		
		//Auto open ns wp admin dashboard
		add_action( 'load-index.php', array( $this, 'ns_redirect_dashboard' ) );
		
		//Enqueue plugin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 100 );
		
		//ajax handler for plugins management
		add_action( 'wp_ajax_plugin_activation_link', array( $this, 'plugin_activation_link' ) );
		
		//Custom ns wp admin plugin notifications centre
		add_action( 'wp_ajax_update_plugin_note', array( $this, 'update_plugin_note' ) );
		
		//Task approve
		add_action( 'wp_ajax_task_approve', array( $this, 'task_approve' ) );
		
		// Sidebar management capabilities ajax handler
		add_action( 'wp_ajax_ajax_set_user_menu_dashboard', array( $this, 'ajax_set_user_menu_dashboard' ) );
		
		add_action( 'wp_ajax_set_menu_dashboard', array( $this, 'set_menu_dashboard' ) );
		
		//Documentation repeater ajax
		add_action( 'wp_ajax_documentation_repeater', array( $this, 'documentation_repeater' ) );
		
		//Save documentation ajax
		add_action( 'wp_ajax_documentation_block_save', array( $this, 'documentation_block_save' ) );
		
		//hide all wp notifications
		add_action( 'admin_head', array( $this, 'hide_all_update_messages' ), 1 );
		
		//Set up custom menu icon and menu name
		add_action( 'wp_ajax_set_menu_icon_and_name', array( $this, 'set_menu_icns_add_taskon_and_name' ) );
		
		//add_action( 'admin_head', array($this, 'ns_admin_top_section' ) , 1 );
		
		//hide admin bar for admin panel
		add_action( 'init', array( $this, 'disable_admin_bar' ) );
		
		//create custom post type for new task creation
		add_action( 'init', array( $this, 'create_new_task' ) );
		
		// include GA tracking code before the closing head tag
		//add_action('wp_head', array($this, 'google_analytics_tracking_code' ) );
		
		//save GAPI access code
		add_action( 'wp_ajax_ns_save_gapi_access_code', array( $this, 'ns_save_gapi_access_code' ) );
		
		//Delete GApi access code
		add_action( 'wp_ajax_ns_gapi_disconnect', array( $this, 'ns_gapi_disconnect' ) );
		
		//add task
		add_action( 'wp_ajax_ns_add_task', array( $this, 'add_task' ) );
		
		//add comment
		add_action( 'wp_ajax_ns_add_comment', array( $this, 'add_comment' ) );
		
		//add thread comment
		add_action( 'wp_ajax_ns_add_thread_comment', array( $this, 'add_thread_comment' ) );
		
		//change status
		add_action( 'wp_ajax_ns_change_status', array( $this, 'change_status' ) );
		
		//reorder tasks
		add_action( 'wp_ajax_ns_reorder_tasks', array( $this, 'reorder_tasks' ) );
		
		//order tasks
		add_action( 'wp_ajax_ns_order_tasks', array( $this, 'order_tasks' ) );
		
		//load_next_tasks
		add_action( 'wp_ajax_ns_load_next_tasks', array( $this, 'load_next_tasks' ) );
		
		//tasks_delete
		add_action( 'wp_ajax_ns_task_delete', array( $this, 'ns_task_delete' ) );
		
		//change NS admin footer text
		add_filter( 'admin_footer_text', array( $this, 'ns_plugin_footer_text' ) );
		
		//ns admin add admin body class
		add_filter( 'admin_body_class', array( $this, 'ns_wp_body_classes' ) );
		
		//admin menu page for ns logo
		add_action( 'admin_menu', array( $this, 'ns_menu_logo' ) );
		
		//remove default dashboard
		add_action( 'admin_menu', array( $this, 'ns_remove_default_dashboard' ) );
		
		// TODO: uncomment this if you want to add custom JS
		//add_action( 'admin_print_footer_scripts', array($this, 'add_javascript'), 100 );
		
		// TODO: uncomment this if you want to add custom actions to run on deactivation
		//register_deactivation_hook( __FILE__, array($this, 'deactivate_plugin_actions') );
		
		add_action( 'init', array( $this, 'image_uploader' ) );
		
		
		add_action( 'init', array( $this->task_settings, 'register_statuses' ) );
	}
	
	/**
	 * @return void
	 */
	private function init_plugin_settings() {
		$this->path = plugin_dir_path( __FILE__ );
		// TODO: update to actual
		$this->wp_plugin_page = "http://wordpress.org/plugins/ns-wordpress-plugin-template";
		// TODO: update to link builder generated URL or other public page or redirect
		$this->ns_plugin_page = "http://neversettle.it/";
		// TODO: update this - used throughout plugin code and only have to update here
		$this->ns_admin_plugin = "NS Admin Plugin";
		// TODO: update this - used throughout plugin code and only have to update here
		$this->ns_plugin_menu = "NS Plugin Menu";
		// TODO: update this - used throughout plugin code and only have to update here
		$this->ns_plugin_slug = "ns-admin-plugin";
		// TODO: update this - used throughout plugin code and only have to update here
		$this->ns_plugin_ref = "ns-dashboard";
		
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$this->plugin_info = get_plugin_data( plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'ns-admin-plugin.php' );
		
		$this->plugin_site_info = [
			'site_url'  => get_site_url(),
			'site_name' => get_bloginfo( 'name' ),
		];
	}
	
	/**
	 * Get plugin root dir
	 *
	 * @return string
	 */
	private function get_plugin_root() {
		return $this->path . $this->ns_plugin_slug;
	}
	
	/**
	 * Deactivation actions
	 *
	 * @return void
	 */
	public function deactivate_plugin_actions() {
		// TODO: add any deactivation actions here
	}
	
	/**
	 * Create tasks post type
	 *
	 * @return void
	 */
	public function create_new_task() {
		
		$labels = array(
			'name'               => _x( 'Tasks', 'Post Type General Name', $this->ns_plugin_ref ),
			'singular_name'      => _x( 'Task', 'Post Type Singular Name', $this->ns_plugin_ref ),
			'menu_name'          => __( 'Tasks', $this->ns_plugin_ref ),
			'parent_item_colon'  => __( 'Parent Task', $this->ns_plugin_ref ),
			'all_items'          => __( 'All Tasks', $this->ns_plugin_ref ),
			'view_item'          => __( 'View Task', $this->ns_plugin_ref ),
			'add_new_item'       => __( 'Add New Task', $this->ns_plugin_ref ),
			'add_new'            => __( 'Add New', $this->ns_plugin_ref ),
			'edit_item'          => __( 'Edit Task', $this->ns_plugin_ref ),
			'update_item'        => __( 'Update Task', $this->ns_plugin_ref ),
			'search_items'       => __( 'Search Task', $this->ns_plugin_ref ),
			'not_found'          => __( 'Not Found', $this->ns_plugin_ref ),
			'not_found_in_trash' => __( 'Not found in Trash', $this->ns_plugin_ref ),
		);
		
		$args = array(
			'label'               => __( 'tasks', $this->ns_plugin_ref ),
			'description'         => __( 'Task creation custom post', $this->ns_plugin_ref ),
			'labels'              => $labels,
			// Features this CPT supports in Post Editor
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'comments',
				'revisions',
				'custom-fields',
			),
			// You can associate this CPT with a taxonomy or custom taxonomy.
			'taxonomies'          => array( 'genres' ),
			/* A hierarchical CPT is like Pages and can have
				* Parent and child items. A non-hierarchical CPT
				* is like Posts.
				*/
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		
		// Registering your Custom Post Type
		register_post_type( $this->task_type, $args );
		
	}
	
	/*********************************
	 * NOTICES & LOCALIZATION
	 */
	function setup_plugin() {
		load_plugin_textdomain( $this->ns_plugin_slug, false, $this->path . "lang/" );
	}
	
	function admin_notices() {
		$message = 'Welcome to NS updated admin dashboard';
		if ( $message != '' ) {
			echo "<div class='updated'><p>$message</p></div>";
		}
	}
	
	function admin_assets() {
		
		$css_src = includes_url( 'css/' ) . 'editor.css';
		$js_src  = includes_url( 'js/tinymce/' ) . 'tinymce.min.js';
		wp_register_style( $this->ns_plugin_slug, plugins_url( "css/ns-plugincss.css", __FILE__ ), false, '1.0.1' );
		wp_register_style( 'ns_wp_remodal_default_css', plugins_url( "css/remodal-theme.css", __FILE__ ), false, '1.0.1' );
		wp_register_style( 'ns_wp_remodal_css', plugins_url( "css/remodal.css", __FILE__ ), false, '1.0.1' );
		wp_register_style( 'tinymce_css', $css_src );
		wp_register_style( 'client_task_creation_css', plugins_url( "css/client-task-creation.css", __FILE__ ), false, '1.0.2' );
		wp_register_style( 'ns_amchart_export_css', plugins_url( "css/amchart_export.css", __FILE__ ), false, '1.0.2' );
		wp_register_script( $this->ns_plugin_slug, plugins_url( "js/ns-pluginjs.js", __FILE__ ), false, '1.0.1' );
		
		if ( ! empty( $menu_icon_data = get_site_option( 'ns_wp_menu_icon_data' ) ) ) {
			wp_localize_script( $this->ns_plugin_slug, 'menu_items_data', $menu_icon_data );
		}
		wp_register_script( 'ns_wp_remodal_js', plugins_url( "js/plugins/remodal.js", __FILE__ ), false, '1.0.1' );
		wp_register_script( 'jquety_repeater_js', plugins_url( "js/plugins/jquery.repeater.min.js", __FILE__ ), false, '1.0.1' );
		wp_register_script( 'ga_utils_api', plugins_url( "js/plugins/ga-api-utils.js", __FILE__ ), false, false );
		wp_register_script( 'tinymce_js', $js_src );
		wp_register_script( 'ns_google_analytics', 'https://www.google-analytics.com/analytics.js', false, false );
		wp_register_script( 'ns_google_analytics_api', 'https://apis.google.com/js/client.js?onload=authorize', false, false );
		wp_register_script( 'ns_wp_amchart_js', plugins_url( "js/plugins/amcharts.js", __FILE__ ), false, false );
		wp_register_script( 'ns_wp_amchart_export_js', plugins_url( "js/plugins/export.min.js", __FILE__ ), false, false );
		wp_register_script( 'ns_wp_amchart_serial_js', plugins_url( "js/plugins/serial.js", __FILE__ ), false, false );
		wp_register_script( 'ns_image_uploader', plugins_url( "js/plugins/ns_image_uploader.js", __FILE__ ), false, false );
		wp_register_script( 'ns_timeline', plugins_url( "js/plugins/ns_timeline.js", __FILE__ ), false, false );
		wp_register_script( 'ns_amcharts', plugins_url( "js/plugins/ns_amcharts.js", __FILE__ ), false, false );
		wp_register_script( 'cdnjs-d3', 'https://cdnjs.cloudflare.com/ajax/libs/d3/4.2.2/d3.min.js', false, false );
		wp_register_script( 'd3js', 'https://d3js.org/d3.v4.min.js', false, false );
		wp_register_script( 'scrollbar_js', plugins_url( "js/plugins/jquery.mCustomScrollbar.concat.min.js", __FILE__ ), false, false );
		wp_register_style( 'scrollbar_css', plugins_url( "css/jquery.mCustomScrollbar.min.css", __FILE__ ), false, false );
		wp_register_style( 'ns_grid', plugins_url( "css/grid.css", __FILE__ ), false, false );
		
		wp_enqueue_style( 'scrollbar_css' );
		wp_enqueue_style( $this->ns_plugin_slug );
		wp_enqueue_style( 'ns_wp_remodal_default_css' );
		wp_enqueue_style( 'ns_wp_remodal_css' );
		wp_enqueue_style( 'tinymce_css' );
		wp_enqueue_style( 'client_task_creation_css' );
		wp_enqueue_style( 'ns_grid' );
		
		wp_enqueue_script( 'ns_wp_remodal_js' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( $this->ns_plugin_slug );
		wp_enqueue_script( 'jquety_repeater_js' );
		wp_enqueue_script( 'tinymce_js' );
		wp_enqueue_script( 'ns_google_analytics' );
		wp_enqueue_script( 'ns_google_analytics_api' );
		wp_enqueue_script( 'ns_wp_amchart_js' );
		wp_enqueue_script( 'ns_wp_amchart_export_js' );
		wp_enqueue_script( 'ns_wp_amchart_serial_js' );
		wp_enqueue_script( 'cdnjs-d3' );
		wp_enqueue_script( 'd3js' );
		wp_enqueue_script( 'scrollbar_js' );
		
		$screen             = get_current_screen();
		$timeline_meta_data = get_site_option( 'received_data' );
		$client_timeline    = unserialize( $timeline_meta_data['projects_data'] );
		if ( $screen->base == 'dashboard_page_ns-dashboard' ) {
			wp_enqueue_script( 'ns_amcharts' );
			if ( $client_timeline ) {
				wp_enqueue_script( 'ns_timeline' );
			}
		}
		
		wp_localize_script( $this->ns_plugin_slug, 'ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ajax-nonce' )
		) );
		
		wp_enqueue_media();
		wp_enqueue_script( 'ns_image_uploader' );
	}
	
	
	/*************************************
	 * FUNCTIONALITY
	 */
	public function ns_register_menu() {
		add_dashboard_page( 'Dashboard', 'Dashboard', 'read', 'ns-dashboard', array( $this, 'ns_create_dashboard' ) );
		add_submenu_page(
			null,
			'Task Edit',
			'Task Edit',
			'manage_options',
			'ns_plugin_task_edit_page',
			[ $this, 'ns_task_edit_page' ]
		);
	}
	
	public function ns_redirect_dashboard() {
		
		if ( is_admin() ) {
			$screen = get_current_screen();
			
			if ( $screen->base == 'dashboard' ) {
				
				wp_redirect( admin_url( 'index.php?page=ns-dashboard' ) );
				
			}
		}
		
	}
	
	/**
	 * Create top level menu item for NS logo
	 */
	public function ns_menu_logo() {
		add_menu_page(
			__( 'Logo Section', 'ns_admin_plugin' ),
			'Dashboard',
			'read',
			'ns_admin_logo',
			'',
			plugins_url( 'ns-admin-plugin/images/ns-logo.png' ),
			0
		);
		global $menu;
		$menu[0][2] = '/wp-admin/index.php?page=ns-dashboard';
	}
	
	/**
	 * @return bool
	 */
	public function is_woocommerce_activated() {
		return class_exists( 'woocommerce' );
	}
	
	
	/**
	 * Create dashboard
	 *
	 * @return void
	 */
	public function ns_create_dashboard() {
		
		$this->user_can( 'manage_options' );
		
		/**
		 * custom dashboard page layout
		 */
		
		$this->init_wp_admin_bootstrap();
		
		echo '<div id="ns_dashboard">'; ?>
        <div id="loader-wrapper">
            <div id="loader"></div>
            <div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>
        </div>
		<?php
		$this->ns_admin_top_section();
		
		echo '<div class="wrp_container">';
		
		$this->get_template( 'hub-site-message-section' );
		
		if ( $this->is_woocommerce_activated() ) {
			$this->ns_gapi_section();
		}
		echo '</div>';
		
		echo '<div class="wrp_container">';
		$this->get_template( 'timeline-section' );
		echo '</div>';
		
		$connection_status = get_site_option( 'connection_to_hub_status' );
		$manager_settings  = get_site_option( static::MANAGER_SETTINGS_KEY );
		if ( $connection_status == 'success' && ! empty( $manager_settings ) ):
			echo '<div class="wrp_container">';
			$this->get_template( 'client-task-creation' );
			
			echo '</div>';
		endif;
		echo '<div class="wrp_container">';
		
		$this->get_template( 'help-documentation-section' );
		
		$this->get_template( 'notification-centre' );
		
		echo '</div>';
		
		echo '<div class="wrp_container">';
		
		$this->get_template( 'plugin-display-section' );
		
		$this->get_template( 'sidebar-management' );
		
		echo '<div class="admin_footer_logo">';
		echo '<a href="https://neversettle.it" target="_blank">';
		echo '<img src="' . plugins_url( $this->ns_plugin_slug . '/images/NS_outside_the_box.png' ) . '" alt="logo">';
		echo '</a>';
		echo '</div>';
		
		echo '</div>';
		
		echo '</div>';
	}
	
	/**
	 * Task edit page
	 * return void
	 */
	public function ns_task_edit_page() {
		global $task;
		
		$task_id = $this->sanitize( $_GET, 'edit' );
		/**
		 * @var WP_Post $task
		 */
		$task = get_post( $task_id );
		if ( $task instanceof WP_Error || $task->post_type !== $this->task_type || ! empty( get_post_meta( $task->ID, '_private', true ) ) ) {
			wp_redirect( admin_url( 'index.php?page=ns-dashboard' ) );
		}
		
		$this->get_template( 'client-tasks-edit' );
	}
	
	/**
	 * Init wp admin
	 *
	 * @return void
	 */
	private function init_wp_admin_bootstrap() {
		/** WordPress Administration Bootstrap */
		require_once( ABSPATH . 'wp-load.php' );
		require_once( ABSPATH . 'wp-admin/admin.php' );
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
	}
	
	/**
	 * Get plugins list
	 *
	 * @see lib/templates/plugin-display-section
	 * @return array
	 */
	private function get_plugins_filtered( $all_plugins ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

//        $all_plugins = get_plugins();
		$all_dir_plugins = array();
		$counter         = 0;
		
		foreach ( $all_plugins as $plugin => $array_value ) {
			$plugin_filename = ABSPATH . 'wp-content/plugins/' . $plugin;
			if ( strpos( file_get_contents( $plugin_filename ), 'Plugin Name:' ) !== false ) {
				$active_status                                     = is_plugin_active( $plugin ) ? 'active' : 'inactive';
				$active_link_text                                  = is_plugin_active( $plugin ) ? 'Deactivate: ' : 'Activate: ';
				$all_dir_plugins[ $counter ]['name']               = $array_value['Name'] ? $array_value['Name'] : '';
				$all_dir_plugins[ $counter ]['active_text']        = $active_link_text ? $active_link_text : '';
				$all_dir_plugins[ $counter ]['active_status']      = $active_status ? $active_status : '';
				$all_dir_plugins[ $counter ]['plugin_filename']    = $plugin_filename ? $plugin_filename : '';
				$all_dir_plugins[ $counter ]['plugin_description'] = $array_value['Description'] ? $array_value['Description'] : '';
				$all_dir_plugins[ $counter ]['text_domain']        = $array_value['TextDomain'] ? $array_value['TextDomain'] : '';
				$counter ++;
			}
		}
		
		return $all_dir_plugins;
	}
	
	/*************************************
	 * UITILITY
	 */
	
	/*
		 * Add body classes
		 */
	
	public function ns_wp_body_classes( $classes ) {
		return $classes . " ns_wp_class ";
	}
	
	/**
	 * Remove default wp dashboard from admin menu items
	 */
	function ns_remove_default_dashboard() {
		remove_menu_page( 'index.php' );
	}
	
	/**
	 * Ajax handler for plugin activation link
	 */
	public function plugin_activation_link() {
		
		if ( isset( $_POST['pluginFilepath'] ) ) {
			
			$activeation_status = $_POST['activeStatus'];
			$plugin_basefile    = $_POST['pluginFilepath'];
			$output             = '';
			if ( ! empty( $activeation_status ) && $activeation_status == 'inactive' ) {
				
				activate_plugin( $plugin_basefile );
				$output = '<div class="notice notice-success is-dismissible"><p>Congratulations the plugin was activated on maximum warp speed!</p></div>';
				
			} else {
				
				deactivate_plugins( $plugin_basefile );
				$output = '<div class="notice notice-info is-dismissible"><p>Succssesfully deactivated, waiting for another commands!</p></div>';
				
			}
			
			wp_send_json_success( $output );
			
		}
		
	}
	
	/**
	 * Ajax Handler for plugins notifications
	 */
	public function update_plugin_note() {
		
		if ( isset( $_POST['NevNote'] ) ) {
			
			$note_value  = $_POST['NevNote'];
			$option_name = $_POST['optionName'];
			update_site_option( $option_name, $note_value );
			wp_send_json_success( $note_value );
			
		}
		
	}
	
	/**
	 * Ajax Handler for Admin management tabs
	 */
	public function ajax_set_user_menu_dashboard() {
		
		if ( ! empty( $_POST['roles_obj'] ) ) {
			
			update_site_option( 'sidebar_management_capabilities', $_POST['roles_obj'] );
			
			wp_send_json_success( 'success' );
			
		}
		
	}
	
	/**
	 *
	 * Ajax Handler for admin management tabs outside dashboard page
	 *
	 */
	public function set_menu_dashboard() {
		
		$user_sidebar_capabilities = get_site_option( 'sidebar_management_capabilities' );
		if ( ! empty( $user_sidebar_capabilities ) ) {
			global $wp_roles;
			$roles                                          = $wp_roles->get_names();
			$user_sidebar_capabilities['current_user_role'] = wp_get_current_user()->roles[0];
			wp_send_json_success( $user_sidebar_capabilities );
			
		}
		
	}
	
	public function plugin_image( $filename, $alt = '', $class = '' ) {
		
		echo "<img src='" . plugins_url( "/images/$filename", __FILE__ ) . "' alt='$alt' class='$class' />";
		
	}
	
	/**
	 * change the footer text
	 * */
	public function ns_plugin_footer_text( $text ) {
		$text = '<img class="footer_ns_logo" src="' . plugins_url( 'images/ns-logo.png', __FILE__ ) . '"> Powered by <a href="https://neversettle.it">Never Settle</a>.';
		
		return false;
	}
	
	/**
	 * function to hide all wp notifications
	 */
	public function hide_all_update_messages() {
		
		if ( get_current_screen()->id !== 'dashboard_page_ns-dashboard' ) {
			
			remove_all_actions( 'admin_notices' );
			
		}
		
	}
	
	public function show_admin_notices() {
		if ( is_network_admin() ) {
			/**
			 * Prints network admin screen notices.
			 *
			 * @since 3.1.0
			 */
			do_action( 'network_admin_notices' );
		} elseif ( is_user_admin() ) {
			/**
			 * Prints user admin screen notices.
			 *
			 * @since 3.1.0
			 */
			do_action( 'user_admin_notices' );
		} else {
			/**
			 * Prints admin screen notices.
			 *
			 * @since 3.1.0
			 */
			do_action( 'admin_notices' );
		}
	}
	
	public function custom_admin_menu_icons_css() {
		?>
        <style>
            /* Example: Change Dashboard icon */
            #adminmenu .dashicons-dashboard:before {
                content: "NS";
            }

            /* Add other CSS icons styles */
        </style>
		<?php
	}
	
	/**
	 * Get the URL of an admin menu item
	 *
	 * @param   string $menu_item_file admin menu item file
	 *          - can be obtained via array key #2 for any item in the global $menu or $submenu array
	 * @param   boolean $submenu_as_parent
	 *
	 * @return  string URL of admin menu item, NULL if the menu item file can't be found in $menu or $submenu
	 */
	public function get_admin_menu_item_url( $menu_item_file, $submenu_as_parent = true ) {
		global $menu, $submenu, $self, $parent_file, $submenu_file, $plugin_page, $typenow;
		
		$admin_is_parent = false;
		$item            = '';
		$submenu_item    = '';
		$url             = '';
		
		// 1. Check if top-level menu item
		foreach ( $menu as $key => $menu_item ) {
			if ( array_keys( $menu_item, $menu_item_file, true ) ) {
				$item = $menu[ $key ];
			}
			
			if ( $submenu_as_parent && ! empty( $submenu_item ) ) {
				$menu_hook = get_plugin_page_hook( $submenu_item[2], $item[2] );
				$menu_file = $submenu_item[2];
				
				if ( false !== ( $pos = strpos( $menu_file, '?' ) ) ) {
					$menu_file = substr( $menu_file, 0, $pos );
				}
				if ( ! empty( $menu_hook ) || ( ( 'index.php' != $submenu_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
					$admin_is_parent = true;
					$url             = 'admin.php?page=' . $submenu_item[2];
				} else {
					$url = $submenu_item[2];
				}
			} elseif ( ! empty( $item[2] ) && current_user_can( $item[1] ) ) {
				$menu_hook = get_plugin_page_hook( $item[2], 'admin.php' );
				$menu_file = $item[2];
				
				if ( false !== ( $pos = strpos( $menu_file, '?' ) ) ) {
					$menu_file = substr( $menu_file, 0, $pos );
				}
				if ( ! empty( $menu_hook ) || ( ( 'index.php' != $item[2] ) && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! file_exists( ABSPATH . "/wp-admin/$menu_file" ) ) ) {
					$admin_is_parent = true;
					$url             = 'admin.php?page=' . $item[2];
				} else {
					$url = $item[2];
				}
			}
		}
		
		// 2. Check if sub-level menu item
		if ( ! $item ) {
			$sub_item = '';
			foreach ( $submenu as $top_file => $submenu_items ) {
				
				// Reindex $submenu_items
				$submenu_items = array_values( $submenu_items );
				
				foreach ( $submenu_items as $key => $submenu_item ) {
					if ( array_keys( $submenu_item, $menu_item_file ) ) {
						$sub_item = $submenu_items[ $key ];
						break;
					}
				}
				
				if ( ! empty( $sub_item ) ) {
					break;
				}
			}
			
			// Get top-level parent item
			foreach ( $menu as $key => $menu_item ) {
				if ( array_keys( $menu_item, $top_file, true ) ) {
					$item = $menu[ $key ];
					break;
				}
			}
			
			// If the $menu_item_file parameter doesn't match any menu item, return false
			if ( ! $sub_item ) {
				return false;
			}
			
			// Get URL
			$menu_file = $item[2];
			
			if ( false !== ( $pos = strpos( $menu_file, '?' ) ) ) {
				$menu_file = substr( $menu_file, 0, $pos );
			}
			
			// Handle current for post_type=post|page|foo pages, which won't match $self.
			$self_type = ! empty( $typenow ) ? $self . '?post_type=' . $typenow : 'nothing';
			$menu_hook = get_plugin_page_hook( $sub_item[2], $item[2] );
			
			$sub_file = $sub_item[2];
			if ( false !== ( $pos = strpos( $sub_file, '?' ) ) ) {
				$sub_file = substr( $sub_file, 0, $pos );
			}
			
			if ( ! empty( $menu_hook ) || ( ( 'index.php' != $sub_item[2] ) && file_exists( WP_PLUGIN_DIR . "/$sub_file" ) && ! file_exists( ABSPATH . "/wp-admin/$sub_file" ) ) ) {
				// If admin.php is the current page or if the parent exists as a file in the plugins or admin dir
				if ( ( ! $admin_is_parent && file_exists( WP_PLUGIN_DIR . "/$menu_file" ) && ! is_dir( WP_PLUGIN_DIR . "/{$item[2]}" ) ) || file_exists( $menu_file ) ) {
					$url = add_query_arg( array( 'page' => $sub_item[2] ), $item[2] );
				} else {
					$url = add_query_arg( array( 'page' => $sub_item[2] ), 'admin.php' );
				}
			} else {
				$url = $sub_item[2];
			}
		}
		
		return esc_url( $url );
		
	}
	
	//TODO Should not be here, should be in separate template and/or class with other functions from wp core
	
	/**
	 * @since 2.9.0
	 */
	function list_theme_updates() {
		$themes = get_theme_updates();
		if ( empty( $themes ) ) {
			echo '<h2>' . __( 'Themes' ) . '</h2>';
			echo '<p>' . __( 'Your themes are all up to date.' ) . '</p>';
			
			return;
		}
		
		$form_action = 'update-core.php?action=do-theme-upgrade';
		?>
        <h2><?php _e( 'Themes' ); ?></h2>
        <p><?php _e( 'The following themes have new versions available. Check the ones you want to update and then click &#8220;Update Themes&#8221;.' ); ?></p>
        <p><?php printf( __( '<strong>Please Note:</strong> Any customizations you have made to theme files will be lost. Please consider using <a href="%s">child themes</a> for modifications.' ), __( 'https://codex.wordpress.org/Child_Themes' ) ); ?></p>
        <form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-themes" class="upgrade">
			<?php wp_nonce_field( 'upgrade-core' ); ?>
            <p><input id="upgrade-themes" class="button" type="submit" value="<?php esc_attr_e( 'Update Themes' ); ?>"
                      name="upgrade"/></p>
            <table class="widefat updates-table" id="update-themes-table">
                <thead>
                <tr>
                    <td class="manage-column check-column"><input type="checkbox" id="themes-select-all"/></td>
                    <td class="manage-column"><label for="themes-select-all"><?php _e( 'Select All' ); ?></label></td>
                </tr>
                </thead>

                <tbody class="plugins">
				<?php
				foreach ( $themes as $stylesheet => $theme ) :
					$checkbox_id = 'checkbox_' . md5( $theme->get( 'Name' ) );
					?>
                    <tr>
                        <td class="check-column">
                            <input type="checkbox" name="checked[]" id="<?php echo $checkbox_id; ?>"
                                   value="<?php echo esc_attr( $stylesheet ); ?>"/>
                            <label for="<?php echo $checkbox_id; ?>" class="screen-reader-text"><?php
								/* translators: %s: theme name */
								printf( __( 'Select %s' ),
									$theme->display( 'Name' )
								);
								?></label>
                        </td>
                        <td class="plugin-title"><p>
                                <img src="<?php echo esc_url( $theme->get_screenshot() ); ?>" width="85" height="64"
                                     class="updates-table-screenshot" alt=""/>
                                <strong><?php echo $theme->display( 'Name' ); ?></strong>
								<?php
								/* translators: 1: theme version, 2: new version */
								printf( __( 'You have version %1$s installed. Update to %2$s.' ),
									$theme->display( 'Version' ),
									$theme->update['new_version']
								);
								?>
                            </p></td>
                    </tr>
				<?php
				endforeach;
				?>
                </tbody>

                <tfoot>
                <tr>
                    <td class="manage-column check-column"><input type="checkbox" id="themes-select-all-2"/></td>
                    <td class="manage-column"><label for="themes-select-all-2"><?php _e( 'Select All' ); ?></label></td>
                </tr>
                </tfoot>
            </table>
            <p><input id="upgrade-themes-2" class="button" type="submit" value="<?php esc_attr_e( 'Update Themes' ); ?>"
                      name="upgrade"/></p>
        </form>
		<?php
	}
	
	/**
	 * Get available core updates.
	 *
	 * @param array $options Set $options['dismissed'] to true to show dismissed upgrades too,
	 *                         set $options['available'] to false to skip not-dismissed updates.
	 *
	 * @return array|false Array of the update objects on success, false on failure.
	 */
	function get_core_updates( $options = array() ) {
		$options   = array_merge( array( 'available' => true, 'dismissed' => false ), $options );
		$dismissed = get_site_option( 'dismissed_update_core' );
		
		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}
		
		$from_api = get_site_transient( 'update_core' );
		
		if ( ! isset( $from_api->updates ) || ! is_array( $from_api->updates ) ) {
			return false;
		}
		
		$updates = $from_api->updates;
		$result  = array();
		foreach ( $updates as $update ) {
			if ( $update->response == 'autoupdate' ) {
				continue;
			}
			
			if ( array_key_exists( $update->current . '|' . $update->locale, $dismissed ) ) {
				if ( $options['dismissed'] ) {
					$update->dismissed = true;
					$result[]          = $update;
				}
			} else {
				if ( $options['available'] ) {
					$update->dismissed = false;
					$result[]          = $update;
				}
			}
		}
		
		return $result;
	}
	
	function list_plugin_updates() {
		$wp_version     = get_bloginfo( 'version' );
		$cur_wp_version = preg_replace( '/-.*$/', '', $wp_version );
		
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$plugins = get_plugin_updates();
		if ( empty( $plugins ) ) {
			echo '<h2>' . __( 'Plugins' ) . '</h2>';
			echo '<p>' . __( 'Your plugins are all up to date.' ) . '</p>';
			
			return;
		}
		$form_action = 'update-core.php?action=do-plugin-upgrade';
		
		$core_updates = $this->get_core_updates();
		if ( ! isset( $core_updates[0]->response ) || 'latest' == $core_updates[0]->response || 'development' == $core_updates[0]->response || version_compare( $core_updates[0]->current, $cur_wp_version, '=' ) ) {
			$core_update_version = false;
		} else {
			$core_update_version = $core_updates[0]->current;
		}
		?>
        <h2><?php _e( 'Plugins' ); ?></h2>
        <p><?php _e( 'The following plugins have new versions available. Check the ones you want to update and then click &#8220;Update Plugins&#8221;.' ); ?></p>
        <form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-plugins" class="upgrade">
			<?php wp_nonce_field( 'upgrade-core' ); ?>
            <p><input id="upgrade-plugins" class="button" type="submit" value="<?php esc_attr_e( 'Update Plugins' ); ?>"
                      name="upgrade"/></p>
            <table class="widefat updates-table" id="update-plugins-table">
                <thead>
                <tr>
                    <td class="manage-column check-column"><input type="checkbox" id="plugins-select-all"/></td>
                    <td class="manage-column"><label for="plugins-select-all"><?php _e( 'Select All' ); ?></label></td>
                </tr>
                </thead>

                <tbody class="plugins">
				<?php
				foreach ( (array) $plugins as $plugin_file => $plugin_data ) {
					$plugin_data = (object) _get_plugin_data_markup_translate( $plugin_file, (array) $plugin_data, false, true );
					
					// Get plugin compat for running version of WordPress.
					if ( isset( $plugin_data->update->tested ) && version_compare( $plugin_data->update->tested, $cur_wp_version, '>=' ) ) {
						$compat = '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: 100%% (according to its author)' ), $cur_wp_version );
					} elseif ( isset( $plugin_data->update->compatibility->{$cur_wp_version} ) ) {
						$compat = $plugin_data->update->compatibility->{$cur_wp_version};
						$compat = '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: %2$d%% (%3$d "works" votes out of %4$d total)' ), $cur_wp_version, $compat->percent, $compat->votes, $compat->total_votes );
					} else {
						$compat = '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: Unknown' ), $cur_wp_version );
					}
					// Get plugin compat for updated version of WordPress.
					if ( $core_update_version ) {
						if ( isset( $plugin_data->update->tested ) && version_compare( $plugin_data->update->tested, $core_update_version, '>=' ) ) {
							$compat .= '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: 100%% (according to its author)' ), $core_update_version );
						} elseif ( isset( $plugin_data->update->compatibility->{$core_update_version} ) ) {
							$update_compat = $plugin_data->update->compatibility->{$core_update_version};
							$compat        .= '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: %2$d%% (%3$d "works" votes out of %4$d total)' ), $core_update_version, $update_compat->percent, $update_compat->votes, $update_compat->total_votes );
						} else {
							$compat .= '<br />' . sprintf( __( 'Compatibility with WordPress %1$s: Unknown' ), $core_update_version );
						}
					}
					// Get the upgrade notice for the new plugin version.
					if ( isset( $plugin_data->update->upgrade_notice ) ) {
						$upgrade_notice = '<br />' . strip_tags( $plugin_data->update->upgrade_notice );
					} else {
						$upgrade_notice = '';
					}
					
					$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_data->update->slug . '&section=changelog&TB_iframe=true&width=640&height=662' );
					$details     = sprintf(
						'<a href="%1$s" class="thickbox open-plugin-details-modal" aria-label="%2$s">%3$s</a>',
						esc_url( $details_url ),
						/* translators: 1: plugin name, 2: version number */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $plugin_data->Name, $plugin_data->update->new_version ) ),
						/* translators: %s: plugin version */
						sprintf( __( 'View version %s details.' ), $plugin_data->update->new_version )
					);
					
					$checkbox_id = "checkbox_" . md5( $plugin_data->Name );
					?>
                    <tr>
                        <td class="check-column">
                            <input type="checkbox" name="checked[]" id="<?php echo $checkbox_id; ?>"
                                   value="<?php echo esc_attr( $plugin_file ); ?>"/>
                            <label for="<?php echo $checkbox_id; ?>" class="screen-reader-text"><?php
								/* translators: %s: plugin name */
								printf( __( 'Select %s' ),
									$plugin_data->Name
								);
								?></label>
                        </td>
                        <td class="plugin-title"><p>
                                <strong><?php echo $plugin_data->Name; ?></strong>
								<?php
								/* translators: 1: plugin version, 2: new version */
								printf( __( 'You have version %1$s installed. Update to %2$s.' ),
									$plugin_data->Version,
									$plugin_data->update->new_version
								);
								echo ' ' . $details . $compat . $upgrade_notice;
								?>
                            </p></td>
                    </tr>
					<?php
				}
				?>
                </tbody>

                <tfoot>
                <tr>
                    <td class="manage-column check-column"><input type="checkbox" id="plugins-select-all-2"/></td>
                    <td class="manage-column"><label for="plugins-select-all-2"><?php _e( 'Select All' ); ?></label>
                    </td>
                </tr>
                </tfoot>
            </table>
            <p><input id="upgrade-plugins-2" class="button" type="submit"
                      value="<?php esc_attr_e( 'Update Plugins' ); ?>" name="upgrade"/></p>
        </form>
		<?php
	}
	
	/**
	 * Display upgrade WordPress for downloading latest or upgrading automatically form.
	 *
	 * @since 2.7.0
	 *
	 * @global string $required_php_version
	 * @global string $required_mysql_version
	 */
	function core_upgrade_preamble() {
		global $required_php_version, $required_mysql_version;
		
		$wp_version = get_bloginfo( 'version' );
		$updates    = get_core_updates();
		
		if ( ! isset( $updates[0]->response ) || 'latest' == $updates[0]->response ) {
			echo '<h2>';
			_e( 'You have the latest version of WordPress.' );
			
			if ( wp_http_supports( array( 'ssl' ) ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				$upgrader            = new WP_Automatic_Updater;
				$future_minor_update = (object) array(
					'current'       => $wp_version . '.1.next.minor',
					'version'       => $wp_version . '.1.next.minor',
					'php_version'   => $required_php_version,
					'mysql_version' => $required_mysql_version,
				);
				$should_auto_update  = $upgrader->should_update( 'core', $future_minor_update, ABSPATH );
				if ( $should_auto_update ) {
					echo ' ' . __( 'Future security updates will be applied automatically.' );
				}
			}
			echo '</h2>';
		} else {
			echo '<div class="notice notice-warning"><p>';
			_e( '<strong>Important:</strong> before updating, please <a href="https://codex.wordpress.org/WordPress_Backups">back up your database and files</a>. For help with updates, visit the <a href="https://codex.wordpress.org/Updating_WordPress">Updating WordPress</a> Codex page.' );
			echo '</p></div>';
			
			echo '<h2 class="response">';
			_e( 'An updated version of WordPress is available.' );
			echo '</h2>';
		}
		
		if ( isset( $updates[0] ) && $updates[0]->response == 'development' ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$upgrader = new WP_Automatic_Updater;
			if ( wp_http_supports( 'ssl' ) && $upgrader->should_update( 'core', $updates[0], ABSPATH ) ) {
				echo '<div class="updated inline"><p>';
				echo '<strong>' . __( 'BETA TESTERS:' ) . '</strong> ' . __( 'This site is set up to install updates of future beta versions automatically.' );
				echo '</p></div>';
			}
		}
		
		echo '<ul class="core-updates">';
		foreach ( (array) $updates as $update ) {
			echo '<li>';
			list_core_update( $update );
			echo '</li>';
		}
		echo '</ul>';
		// Don't show the maintenance mode notice when we are only showing a single re-install option.
		if ( $updates && ( count( $updates ) > 1 || $updates[0]->response != 'latest' ) ) {
			echo '<p>' . __( 'While your site is being updated, it will be in maintenance mode. As soon as your updates are complete, your site will return to normal.' ) . '</p>';
		} elseif ( ! $updates ) {
			list( $normalized_version ) = explode( '-', $wp_version );
			echo '<p>' . sprintf( __( '<a href="%s">Learn more about WordPress %s</a>.' ), esc_url( self_admin_url( 'about.php' ) ), $normalized_version ) . '</p>';
		}
		dismissed_updates();
	}
	
	/**
	 * Ajax Handler for creation new documentation block repeater with defaul wp_editon screen
	 */
	public function documentation_repeater() {
		$textarea_id = 'custom_id-' . mt_rand( 100000, 999999 );
		$output      = [];
		ob_start(); ?>
        <div data-repeater-item="<?php echo $textarea_id; ?>" class="repeater_item">
            <div class="ns_doc_title_block" data-remodal-target="<?php echo $textarea_id; ?>">
                <h2>Title</h2>
            </div>
            <div class="ns_doc_edit remodal" data-remodal-id="<?php echo $textarea_id; ?>">
                <div class="doc_header">
                    <button data-remodal-action="close" class="close_users_modal dashicons-arrow-left-alt2">Back
                    </button>
                </div>
                <label>
                    <h3>Doc Title</h3>
                    <input type="text" name="documentation_editor" class="documentation_title" style="width:100%;">
                </label>
				<?php wp_editor( '', $textarea_id, $settings = array( 'textarea_rows' => 13 ) ); ?>
                <input data-repeater-delete class="remove-row" type="button" value="Delete"/>
                <button class="save_documentation">Save</button>
            </div>
        </div>
		<?php
		$html = ob_get_contents();
		ob_clean();
		$output['created_id']   = $textarea_id;
		$output['created_html'] = $html;
		wp_send_json_success( $output );
		
	}
	
	public function documentation_block_save() {
		if ( ! empty( $_POST['content'] ) ) {
			foreach ( $_POST['content'] as $tab_key => $tab ) {
				foreach ( $tab as $editor_key => $editor_content ) {
					foreach ( $editor_content as $title => $description ) {
						$ns_wp_documentation = get_site_option( 'ns_admin_documentation' );
						if ( isset( $ns_wp_documentation[ $tab_key ] ) ) {
							$_POST['content'][ $tab_key ][ $editor_key ][ $title ] = stripslashes( $description );
						} else {
							$_POST['content'][ $tab_key ][ $editor_key ][ $title ] = stripslashes( $description );
						}
					}
				}
			}
			
			update_site_option( 'ns_admin_documentation', $_POST['content'] );
			$output = 'saved';
		} else {
			update_site_option( 'ns_admin_documentation', ' ' );
			$output = 'deleted';
		}
		wp_send_json_success( $output );
	}
	
	/**
	 * Top NS dashboard section
	 *
	 * @return void
	 */
	public function ns_admin_top_section() {
		$this->get_template( 'ns_admin_top_section' );
	}
	
	public function disable_admin_bar() {
		if ( is_blog_admin() ) {
			add_filter( 'show_admin_bar', '__return_false' );
			//remove_action('wp_head', '_admin_bar_bump_cb');
		}
	}
	
	function ajax_verify_connection_to_remote_site() {
		//TODO refactor this
		$this->set_post_data();
		
		$manager_url = ! empty( $_POST['enteredUrl'] ) ? $_POST['enteredUrl'] : wp_send_json_error( [ 'message' => 'Api url can not be empty' ] );
		$manager_key = ! empty( $_POST['enteredKey'] ) ? $_POST['enteredKey'] : wp_send_json_error( [ 'message' => 'Api key can not be empty' ] );
		
		$this->load_settings( $manager_key, $manager_url );
		$connection_data = $this->prepare_connection_data();
		
		$url = trailingslashit( $connection_data['connection_url'] ) . 'wp-admin/admin-ajax.php';
		
		$remote_site_response = unserialize( $this->request_to_remote_site( $url, $connection_data ) );
		
		$not_connected_tasks          = unserialize( $remote_site_response['client_connection_data']['not_connected_tasks'] );
		$not_connected_tasks_comments = unserialize( $not_connected_tasks['comment_data'] );
		ksort( $not_connected_tasks_comments );
		
		
		if ( $remote_site_response === false ) {
			$response = array(
				'error' => 1,
				'body'  => $this->error,
			);
			@header( 'Content-Type: application/json; charset=' . get_site_option( 'blog_charset' ) );
			$this->ajax_exit( json_encode( $response ) );
		}
		update_site_option( 'connection_to_hub_status', 'error' );
		if ( isset( $remote_site_response['success'] ) && $remote_site_response['success'] == 1 ) {
			
			if ( ! empty( $not_connected_tasks['task_data'] ) ) {
				foreach ( $not_connected_tasks['task_data'] as $task_data ) {
					$task_data          = unserialize( $task_data );
					$task_data['inner'] = false;
					$this->log( $task_data );
					$response = $this->add_task_inner( $task_data );
					$this->log( 'add task resp' );
					$this->log( $response );
				}
			}
			
			if ( ! empty( $not_connected_tasks_comments ) ) {
				foreach ( $not_connected_tasks_comments as $comment_data ) {
					$comment_data['inner'] = false;
					$this->log( $comment_data );
					$response = $this->add_comment_inner( $comment_data );
				}
			}

//		    $remote_site_response['current_site_url'] = preg_replace( '#^http(s)?:#', '', get_site_url() );
			$remote_site_response['current_site_url']  = get_site_url();
			$remote_site_response['current_site_path'] = get_home_path();
			@header( 'Content-Type: application/json; charset=' . get_site_option( 'blog_charset' ) );
			
			if ( ! empty( $remote_site_response['main_hub_message'] ) ) {
				update_site_option( 'main_message_from_hub_site', $remote_site_response['main_hub_message'] );
			}
			if ( isset( $remote_site_response['client_connection_data'] ) ) {
				update_site_option( 'received_data', $remote_site_response['client_connection_data'] );
			}
			update_site_option( 'connection_to_hub_status', 'success' );
			
			$this->ajax_exit( json_encode( $remote_site_response ) );
		}
	}
	
	/**
	 * Prepare data before connection to remote
	 *
	 * @return array
	 */
	private function prepare_connection_data() {
		
		if ( empty( $this->plugin_site_info ) ) {
			$this->log( 'Plugin site is missing' . __LINE__ );
		}
		
		if ( empty( $this->plugin_info ) ) {
			$this->log( 'Plugin info is missing' . __LINE__ );
		}
		
		$settings = $this->get_settings();
		
		$data = array(
			'action'                => 'nsc_verify_connection_to_remote_site',
			'referer'               => $this->get_short_url( home_url() ),
			'plugin_version'        => $this->plugin_info['Version'],
			'plugin_site_info'      => serialize( $this->plugin_site_info ),
			'client_connection_key' => $settings['connection_key'],
		);


//        $connection_key = !empty($settings['connection_key']) ? $settings['connection_key'] : wp_send_json_error(['message' => 'Connection url in the settings can not be empty']);
		$connection_url = ! empty( $settings['connection_url'] ) ? $settings['connection_url'] : wp_send_json_error( [ 'message' => 'Connection in the settings key can not be empty' ] );
		
		$this->log( 'prepared data --- ' );
		$this->log( $data );
		$data['signature']      = $this->encrypt_connection_key( $data, $settings['connection_key'] );
		$data['connection_url'] = $connection_url;

//        $data = array_merge($data, $settings);
		
		return $data;
	}
	
	/**
	 * Set post data to inner property
	 *
	 * @return void
	 */
	private function set_post_data() {
		$this->post_data = $_POST;
		if ( isset( $_POST['case_sensitive'] ) && $_POST['case_sensitive'] === 'true' ) {
			$this->post_data['case_sensitive'] = true;
		} elseif ( isset( $_POST['case_sensitive'] ) && $_POST['case_sensitive'] === 'false' ) {
			$this->post_data['case_sensitive'] = false;
		}
	}
	
	/**
	 * Ajax exit
	 *
	 * @param $response
	 *
	 * @return void
	 */
	private function ajax_exit( $response ) {
		echo ( $response === false ) ? '' : $response;
		wp_die();
	}
	
	/**
	 * @param $url
	 * @param $data
	 *
	 * @return bool|string
	 */
	private function request_to_remote_site( $url, $data ) {
		
		$args = array(
			'timeout'  => 60,
			'blocking' => true
		);
		
		$args['method'] = 'POST';
		
		if ( ! isset( $args['body'] ) ) {
			$args['body'] = $this->array_to_multipart( $data );
		}
		
		$args['headers']['Content-Type']     = 'multipart/form-data; boundary=' . $this->multipart_boundary;
		$args['headers']['Referer']          = $this->referer_from_url( $url );
		$args['headers']['Content-Encoding'] = "gzip";
//        print_r($args);die();
		$response = wp_remote_post( $url, $args );
		
		$body = unserialize( $response['body'] );
		
		if ( ! is_wp_error( $response ) ) {
			$body = trim( $body, "\xef\xbb\xbf" );
		}
		
		if ( is_wp_error( $response ) ) {
			
			if ( strpos( $url, 'https://' ) === 0 ) {
				return $this->retry_request_to_remote_site( $url, $data );
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && strpos( $response->errors['http_request_failed'][0], 'timed out' ) ) {
				$this->error = 'Connection to remote site has timed out';
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && ( strpos( $response->errors['http_request_failed'][0], 'Could not resolve host' ) || strpos( $response->errors['http_request_failed'][0], "couldn't connect to host" ) ) ) {
				$this->error = sprintf( '%s could find. Please double check URL', $this->post_data['url'] );
			} else {
				$this->error = sprintf( 'Connection to %s failed with unexpected error', $this->post_data['url'] );
			}
			
			return false;
			
		}
		
		return $response['body'];
	}
	
	function array_to_multipart( $data ) {
		
		if ( ! $data || ! is_array( $data ) ) {
			return $data;
		}
		
		$result = '';
		
		foreach ( $data as $key => $value ) {
			$result .= '--' . $this->multipart_boundary . "\r\n" . sprintf( 'Content-Disposition: form-data; name="%s"',
					$key );
			
			if ( 'fragment' == $key ) {
				if ( $data['fragment_gzipped'] ) {
					$result .= "; filename=\"fragment.txt.gz\"\r\nContent-Type: application/x-gzip";
				} else {
					$result .= "; filename=\"fragment.txt\"\r\nContent-Type: text/plain;";
				}
			} else {
				$result .= "\r\nContent-Type: text/plain; charset=" . get_site_option( 'blog_charset' );
			}
			
			$result .= "\r\n\r\n" . $value . "\r\n";
		}
		
		$result .= '--' . $this->multipart_boundary . "--\r\n";
		
		return $result;
	}
	
	function referer_from_url( $referer_url ) {
		$url_parts = $this->parse_url( $referer_url );
		
		if ( false !== $url_parts ) {
			$reduced_url_parts = array_intersect_key( $url_parts, array_flip( array(
				'scheme',
				'host',
				'port',
				'path'
			) ) );
			if ( ! empty( $reduced_url_parts ) ) {
				$referer_url = $this->unparse_url( $reduced_url_parts );
			}
		}
		
		return $referer_url;
	}
	
	function retry_request_to_remote_site( $url, $data ) {
		$url = str_replace( 'https', 'http', $url );
		if ( $response = $this->request_to_remote_site( $url, $data ) ) {
			return $response;
		}
		
		return false;
	}
	
	function get_short_url( $url ) {
		return untrailingslashit( str_replace( array( 'https://', 'http://', '//' ), '', $url ) );
	}
	
	function parse_url( $url ) {
		if ( 0 === strpos( $url, '//' ) ) {
			$url       = 'http:' . $url;
			$no_scheme = true;
		} else {
			$no_scheme = false;
		}
		
		$parts = parse_url( $url );
		if ( $no_scheme ) {
			unset( $parts['scheme'] );
		}
		
		return $parts;
	}
	
	function unparse_url( $parsed_url ) {
		$scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
		$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
		$pass     = ( $user || $pass ) ? "$pass@" : '';
		$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
		$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';
		
		return "$scheme$user$pass$host$port$path$query$fragment";
	}
	
	function encrypt_connection_key( $data, $key ) {
		if ( isset( $data['signature'] ) ) {
			unset( $data['signature'] );
		}
		$data = array_map( array( $this, 'bool_to_string' ), $data );
		
		$single_string = implode( '', $data );
		$this->log( '$single_string ' . $single_string );
		
		return base64_encode( hash_hmac( 'md5', $single_string, $key, true ) );
	}
	
	function bool_to_string( $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? 'true' : 'false';
		}
		
		return $value;
	}
	
	/**
	 * Ajax to get data from manager site
	 */
	
	function respond_to_verify_generate_new_url() {
		$response = array();
		
		$this->post_data             = $_POST;
		$filtered_data               = $this->filter_income_data( $this->post_data, array(
			'action',
			'plugin_version',
			'referer',
			'connection',
		) );
		$settings                    = $this->get_settings();
		$filtered_data['connection'] = stripcslashes( $filtered_data['connection'] );
		if ( ! $this->verify_key( $filtered_data, $settings['connection_key'] ) ) {
			$response['error']      = 1;
			$response['error_slug'] = 'verification_error';
			$response['message']    = 'Verification error! Please be sure that you use correct connection key';
			$response['filtered']   = $filtered_data;
			$this->ajax_exit( serialize( $response ) );
		}
		$response['success'] = 1;
		$response['message'] = 'Connection accepted!';
		$response['message'] = $filtered_data['connection'];
		
		$connection_status = update_site_option( 'connection_to_hub_status', 'error' );
		
		$this->ajax_exit( serialize( $response ) );
	}
	
	function respond_to_verify_connection_to_remote_site() {
		$response = array();
		
		$this->post_data = $_POST;
		$filtered_data   = $this->filter_income_data( $this->post_data, [
			'action',
			'plugin_version',
			'referer',
			'projects_data',
			'client_message',
			'active_services',
			'available_services',
			'task_data',
			'task_data_status',
			'task_data_change',
			'comment_data',
			'partner_data',
			'task_data_delete',
			'tasks_order'
		] );
		
		$settings = $this->get_settings();
		
		$filtered_data['projects_data']      = stripcslashes( $filtered_data['projects_data'] );
		$filtered_data['client_message']     = stripcslashes( $filtered_data['client_message'] );
		$filtered_data['available_services'] = stripcslashes( $filtered_data['available_services'] );
		$filtered_data['active_services']    = stripcslashes( $filtered_data['active_services'] );
		$filtered_data['task_data']          = stripcslashes( $filtered_data['task_data'] );
		$filtered_data['task_data_status']   = stripcslashes( $filtered_data['task_data_status'] );
		$filtered_data['task_data_change']   = stripcslashes( $filtered_data['task_data_change'] );
		$filtered_data['comment_data']       = stripcslashes( $filtered_data['comment_data'] );
		$filtered_data['partner_data']       = stripcslashes( $filtered_data['partner_data'] );
		$filtered_data['task_data_delete']   = stripcslashes( $filtered_data['task_data_delete'] );
		$filtered_data['tasks_order']        = stripcslashes( $filtered_data['tasks_order'] );
		
		$this->log( $filtered_data );
		$this->log( $settings['connection_key'] );
		
		if ( ! $this->verify_key( $filtered_data, $settings['connection_key'] ) ) {
			$response['error']      = 1;
			$response['error_slug'] = 'verification_error';
			$response['message']    = 'Verification error! Please be sure that you use correct connection key';
			$response['key']        = $settings['client_connection_key'];
			$response['filtered']   = $filtered_data;
			
			$this->ajax_exit( serialize( $response ) );
		}
		
		$response['success'] = 1;
		$response['message'] = 'Connection accepted!';
//          $response['key'] = $settings['connection_key'];
		
		$task_data        = unserialize( $filtered_data['task_data'] );
		$comment_data     = unserialize( $filtered_data['comment_data'] );
		$task_data_status = unserialize( $filtered_data['task_data_status'] );
		$task_data_change = unserialize( $filtered_data['task_data_change'] );
		$task_data_delete = unserialize( $filtered_data['task_data_delete'] );
		$task_hub_order   = unserialize( $filtered_data['tasks_order'] );
		
		if ( ! empty( $task_hub_order ) && count( $task_hub_order['order_data'] ) > 0 ) {
		    global $wpdb;
			$task_hub_order['inner'] = false;
			foreach ( $task_hub_order['order_data'] as $hub_task ) {
				$task_query  = "
                        SELECT post_id
                        FROM $wpdb->postmeta meta
                        WHERE meta.meta_key = '_task_id_hub'
                        AND meta.meta_value = {$hub_task['id']}
                    ";
				$hub_task_id = $wpdb->get_var( $task_query );
				if ( $hub_task_id ) {
					$task_id = wp_update_post( [
						'ID'         => $hub_task_id,
						'menu_order' => $hub_task['order'],
					] );
				}
			}
			update_site_option( 'tasks_order_option', $task_hub_order );
			$this->ajax_exit( serialize( $response ) );
		}
		
		//TODO received_data should NOT conatains all data .... because if we will rewrite this field we will broke everything !!!!!!!!!! tmp hack for tasks
		if ( empty( $task_data ) && empty( $comment_data ) && empty( $task_data_status ) && empty( $task_data_change ) && empty( $task_data_delete ) ) {
			update_site_option( 'received_data', $filtered_data );
		}
		if ( ! empty( $task_data ) ) {
			$task_data['inner'] = false;
			$this->log( $task_data );
			$response = array_merge( $response, $this->add_task_inner( $task_data ) );
			$this->log( 'add task resp' );
			$this->log( $response );
		}
		
		if ( ! empty( $comment_data ) ) {
			$comment_data['inner'] = false;
			$this->log( $comment_data );
			$response = array_merge( $response, $this->add_comment_inner( $comment_data ) );
		}
		
		if ( ! empty( $task_data_status ) ) {
			$task_data_status['inner'] = false;
			$this->log( $task_data_status );
			$response = array_merge( $response, $this->change_status_inner( $task_data_status ) );
		}
		
		if ( ! empty( $task_data_change ) ) {
			$this->log( $task_data_status );
			$response = array_merge( $response, $this->update_task_inner( $task_data_change ) );
		}
		
		if ( isset( $task_data_delete ) ) {
			$task_data_delete['inner'] = false;
			$response['deleted']       = 'Hub task number' . $task_data_delete['_task_id_hub'] . 'was deleted';
			wp_delete_post( $task_data_delete['_task_id_client'], true );
		}
		
		$this->ajax_exit( serialize( $response ) );
	}
	
	/**
	 * @param array $task_data_change
	 *
	 * @return array
	 */
	private function update_task_inner( array $task_data_change ) {
		$task     = get_post( $task_data_change['_task_id_client'] );
		$response = [];
		if ( ! ( $task instanceof WP_Post ) ) {
			$response['error']      = 1;
			$response['error_slug'] = 'task_search_inner_error';
			$response['message']    = 'Task for the changing status was not found on the client site.';
			
			return $response;
		}
		
		$private = $this->sanitize( $task_data_change, '_private' );
		if ( $private ) {
			update_post_meta( $task->ID, '_private', $private );
		}
		
		$project = $this->sanitize( $task_data_change, '_project_id' );
		if ( $project ) {
			update_post_meta( $task->ID, '_project_id', $project );
		}
		
		$assignee = $this->sanitize( $task_data_change, '_assignee' );
		if ( $assignee ) {
			update_post_meta( $task->ID, '_assignee', $assignee );
		}
		
		$assignee_name = $this->sanitize( $task_data_change, '_assignee_name' );
		if ( $assignee_name ) {
			update_post_meta( $task->ID, '_assignee_name', $assignee_name );
		}
		
		update_post_meta( $task->ID, '_reporter', $this->sanitize( $task_data_change, '_reporter' ) );
		update_post_meta( $task->ID, '_reporter_name', $this->sanitize( $task_data_change, '_reporter_name' ) );
		
		return $response;
	}
	
	
	/**
	 * @param array $task_data_status
	 *
	 * @return array
	 */
	private function change_status_inner( array $task_data_status ) {
		$task = get_post( $task_data_status['_task_id_client'] );
		
		if ( ! ( $task instanceof WP_Post ) ) {
			if ( ! empty( $task_data_status['inner'] ) ) {
				$response['error']      = 1;
				$response['error_slug'] = 'task_search_inner_error';
				$response['message']    = 'Task for the changing status was not found on the client site.';
				
				return $response;
			} else {
				wp_send_json_error( [ 'errors' => [ 'Task has not found' ] ] );
			}
		}
		
		$task_data_status['ID']           = $task->ID;
		$task_data_status['post_status']  = $this->sanitize( $task_data_status, 'post_status' );
		$task_data_status['_task_id_hub'] = get_post_meta( $task->ID, '_task_id_hub', true );
		
		if ( $task->post_status == $this->task_settings->get_closed_statuses()[0] && $task_data_status['post_status'] != $task->post_status ) {
			delete_post_meta( $task->ID, 'task_live_approved' );
		}
		
		$post_id = wp_update_post( $task_data_status, true );
		
		$response = [];
		
		if ( $post_id instanceof WP_Error ) {
			$response['error']      = 1;
			$response['error_slug'] = 'task_status_update_error';
			$response['message']    = $post_id->get_error_messages();
		} else {
			update_post_meta( $task->ID, '_status_changed_time', time() );
			
			if ( ! empty( $task_data_status['inner'] ) ) {
				
				$response = $this->add_data_on_remote_site( [ 'task_data_status' => serialize( $task_data_status ) ] );
				$this->log( $response );
			}
		}
		
		return $response;
	}
	
	/**
	 * @return void
	 */
	public function change_status() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		global $wpdb;
		
		$task_data_status['inner']           = true;
		$task_data_status['post_status']     = $this->sanitize( $_POST, 'status' );
		$task_data_status['_task_id_client'] = (int) $this->sanitize( $_POST, 'taskId' );
		
		if ( ! in_array( $task_data_status['post_status'], $this->task_settings->get_statuses() ) ) {
			wp_send_json_error( [ 'errors' => [ 'Status is not correct' ] ] );
		}
		
		if ( ! $this->task_settings->is_available_for_client( $task_data_status['post_status'] ) ) {
			wp_send_json_error( [ 'errors' => [ 'Status is not available for changing' ] ] );
		}
		
		//Start transaction
		$wpdb->query( "START TRANSACTION" );
		
		$response = $this->change_status_inner( $task_data_status );
		
		if ( ! empty( $response['error'] ) ) {
			$wpdb->query( "ROLLBACK" );
			
			wp_send_json_error( [ 'errors' => [ ! empty( $response['message'] ) ? $response['message'] : '' ] ] );
		} else {
			$wpdb->query( "COMMIT" );
		}
		
		wp_send_json_success( [ 'remote_response_data' => $response ] );
	}
	
	
	/**
	 * Load settings
	 *
	 * @param $manager_key
	 * @param $manager_url
	 *
	 * @return void
	 */
	private function load_settings( $manager_key, $manager_url ) {
		$this->settings = [
			'connection_key' => $manager_key,
			'connection_url' => $manager_url,
//				'client_connection_key' => $this->generate_connection_key()
		];
		update_site_option( static::MANAGER_SETTINGS_KEY, $this->settings );
	}
	
	/**
	 * Get settings
	 * @return array
	 */
	private function get_settings() {
		if ( empty( $this->settings ) ) {
			$this->settings = get_site_option( static::MANAGER_SETTINGS_KEY );
		}
		
		return $this->settings;
	}
	
	function filter_income_data( $post_array, $accepted_elements ) {
		$accepted_elements[] = 'signature';
		
		return array_intersect_key( $post_array, array_flip( $accepted_elements ) );
	}
	
	function verify_key( $income_data, $key ) {
		if ( empty( $income_data['signature'] ) ) {
			return false;
		}
		
		$new_signature = $this->encrypt_connection_key( $income_data, $key );
		$this->log( '$new_signature ' . $new_signature );
		$this->log( '$income_data sing ' . $income_data['signature'] );
		
		return $new_signature === $income_data['signature'];
	}
	
	
	//TODO refactor this.....Should be in separate template or even in separate  submodule With it's own class
	public function ns_gapi_section() {
//	    $this->init_google_client();
		if ( ! empty( $this->analytics ) ) {
			$profile = $this->getFirstProfileId();
		} else { ?>
            <div id="ga_chart_section">
                <div class="gapi_authorization_wrp">

                    <div class="ns_analyitcs_section">
						<?php
						$auth_url = $this->client->createAuthUrl( Google_Service_Analytics::ANALYTICS_READONLY );
						if ( ! empty( $auth_url ) ) {
							echo "<a href='$auth_url' target='_blank'>" . __( 'Get your google analytics authorization code', 'ns_admin' ) . "</a>";
						}
						$access_code = get_site_option( 'gapi_access_code' );
						?>
                        <br>
                        <input type="text" name="access_code" id="access_code" value="<?php echo $access_code; ?>">
                        <button id="submit_access_code">Submit Code</button>
                    </div>
                    <button class="gapi_connect">Connect to Analytics</button>
                </div>
            </div>
			
			<?php
			return;
		}
		
		/**
		 * Query the Analytics data
		 */
		
		
		$start_date = ! empty( $_GET['date_period'] ) ? date( 'Y-m-01', strtotime( $_GET['date_period'] ) ) : date( 'Y-m-01' );
		$end_date   = date( 'Y-m-d' );
		if ( ! empty( $_GET['date_period'] ) ) {
			$today_date = date( 'Y-m' );
			if ( $_GET['date_period'] != $today_date ) {
				$end_date = date( 'Y-m-t', strtotime( $_GET['date_period'] ) );
			}
		}
		
		$dimensions = 'ga:year,ga:month,ga:day';
		$results    = $this->analytics->data_ga->get(
			'ga:' . $profile,
			$start_date, //'360daysAgo',
			$end_date, //'today',
			'ga:sessions,ga:users,ga:pageviews',
			array(
				'dimensions' => $dimensions,
				'sort'       => 'ga:year,ga:month',
			) );
		$rows       = $results->getRows();
		$data       = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$data[] = array(
					'year'      => $row[0],
					'month'     => $row[1],
					'day'       => $row[2],
					'sessions'  => $row[3],
					'users'     => $row[4],
					'pageviews' => $row[5]
				);
			}
		}
		
		$date_period   = isset( $_GET['date_period'] ) ? $_GET['date_period'] : $end_date;
		$page_url      = admin_url( 'index.php?page=ns-dashboard' );
		$next_page_url = $page_url . '&date_period=' . date( 'Y-m', strtotime( $date_period . ' + 1 month' ) );
		if ( isset ( $_GET['date_period'] ) ) {
			$prev_page_url = $page_url . '&date_period=' . date( 'Y-m', strtotime( $date_period . ' - 1 month' ) );
		} else {
			$prev_page_url = $page_url . '&date_period=' . date( 'Y-m', strtotime( date( 'Y-m' ) . ' -1 month' ) );
		}
		?>
        <div id="ga_chart_section">
			
			<?php if ( count( $data ) > 0 ) : ?>
				
				<?php
				$month_totals = 0;
				$max_users    = 0;
				$max_sales    = 0;
				$total_orders = 0;
				global $wpdb;
//                echo '<pre>';
//	                print_r($data);
//                echo '</pre>';
				?>

                <table class="wp-list-table widefat" style="display: none;">
                    <tr>
						<?php foreach ( $data as $datum ) : ?>
                            <td class="ns_day"
                                data_day="<?php echo $datum['year'] . '-' . $datum['month'] . '-' . $datum['day']; ?>"><?php echo date( 'Y-m' ) . '-' . $datum['day']; ?></td>
						<?php endforeach; ?>
                    </tr>
                    <tr>
						<?php foreach ( $data as $datum ) : ?>
                            <td>
                                <span class="ns_users"
                                      data_users="<?php echo $datum['users']; ?>"><?php echo 'Users: ' . $datum['users']; ?></span>
                                <br>
								<?php echo 'Sesions: ' . $datum['sessions']; ?>
                                <br>
								<?php $month_totals += $datum['users']; ?>
								<?php if ( $datum['users'] > $max_users ) {
									$max_users = $datum['users'];
								} ?>
								<?php $order_date = $datum['year'] . '-' . $datum['month'] . '-' . $datum['day']; ?>
								<?php
								$query  = "SELECT meta.meta_value FROM {$wpdb->postmeta} meta INNER JOIN {$wpdb->posts} posts ON meta.post_id = posts.ID WHERE posts.post_type = 'shop_order' AND meta.meta_key = '_order_total' AND posts.post_date LIKE '{$order_date}%'";
								$orders = $wpdb->get_results( $query ); ?>
								<?php
								$orders_sum = 0;
								if ( ! empty( $orders ) ) {
									foreach ( $orders as $order ) {
										$orders_sum += floatval( $order->meta_value );
									}
								}
								?>
                                <span class="ns_sales"
                                      data-sales="<?php echo $orders_sum; ?>"><?php echo 'Sales: ' . ( function_exists( 'wc_price' ) ? wc_price( $orders_sum ) : $orders_sum ); ?></span>
								<?php
								if ( $orders_sum > $max_sales ) {
									$max_sales = $orders_sum;
								}
								$total_orders += $orders_sum;
								?>
                            </td>
						<?php endforeach; ?>
                    </tr>
                </table>
			<?php endif; ?>
            <div class="ns_gapi_section">
                <div class="chart_header">
                    <h3>
                        <span class="visitor_vs_rev"><?php echo __( 'Vistors vs. Revenue', 'ns_admin' ); ?></span>
                        <a class="gapi_disconnect">Disconnect</a>
                        <p>Monthly:
							<?php echo __( 'VISITORS', 'ns_admin' ); ?><span
                                    class="users">[<?php echo number_format( $month_totals ); ?>]</span>
                            vs
							<?php echo __( 'REVENUE', 'ns_admin' ); ?><span
                                    class="revenue">[$<?php echo floor( $total_orders ); ?>]</span>
                        </p>
                    </h3>
                    <div class="gapi_filter_wrp analytics_filter">
                        <!--                            <div class="legends_col">-->
                        <!--                                <div class="legend visitors">Visitors</div>-->
                        <!--                                <div class="legend sales">Sales</div>-->
                        <!--                            </div>-->
                        <div class="filter_col">
                            <!--                                <span>Filter by:</span>-->
                            <ul class="gapi_filter analytics_filter">
								<?php
								if ( isset( $_GET['date_period'] ) ) {
									if ( $_GET['date_period'] == date( 'Y-m', strtotime( date( 'Y-m' ) . ' -1 month' ) ) ) {
										$month_selection = 'Last Month';
									} else {
										$month_selection = date( 'M Y', strtotime( $_GET['date_period'] ) );
									}
								} else {
									$month_selection = 'This Month';
								}
								
								?>
                                <li class="init"><a href=""><?php echo $month_selection; ?></a></li>
                                <li class="select"><a
                                            href="<?php echo $page_url; ?>"><?php echo __( 'This Month', 'ns_admin' ); ?></a>
                                </li>
                                <li class="select"><a
                                            href="<?php echo $page_url . '&date_period=' . date( 'Y-m', strtotime( date( 'Y-m' ) . ' -1 month' ) ); ?>"><?php echo __( 'Last Month', 'ns_admin' ); ?></a>
                                </li>
                                <li class="select"><a
                                            href="<?php echo $page_url . '&date_period=' . date( 'Y-m', strtotime( date( 'Y-m' ) . ' -2 month' ) ); ?>"><?php echo date( 'M Y', strtotime( date( 'M Y' ) . ' -2 month' ) ); ?></a>
                                </li>
                                <li class="select"><a
                                            href="<?php echo $page_url . '&date_period=' . date( 'Y-m', strtotime( date( 'Y-m' ) . ' -3 month' ) ); ?>"><?php echo date( 'M Y', strtotime( date( 'M Y' ) . ' -3 month' ) ); ?></a>
                                </li>
                                <li class="select"><a
                                            href="<?php echo $page_url . '&date_period=' . date( 'Y-m', strtotime( date( 'Y-m' ) . ' -4 month' ) ); ?>"><?php echo date( 'M Y', strtotime( date( 'M Y' ) . ' -4 month' ) ); ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div id="chartdiv"></div>
                <div class="chart_nav">
					
					<?php
					if ( isset ( $_GET['date_period'] ) ) {
						$prev_page_name = date( 'Y M', strtotime( $date_period ) );
					} else {
						$prev_page_name = date( 'Y M' );
					}
					?>
                    <a href="<?php echo $prev_page_url; ?>" class="dashicons-arrow-left prev"></a>
                    <!--                <a href="--><?php //echo $page_url; ?><!--">Current Month</a>-->
					<?php if ( strtotime( $end_date ) <= strtotime( date( 'Y-m' ) ) ) : ?>
                        <a href="<?php echo $next_page_url; ?>" class="dashicons-arrow-right next"></a>
					<?php endif; ?>
                </div>
            </div>
			
			<?php
			
			//echo'<pre>';
			//print_r($data);
			//echo'</pre>';
			
			$months = [];
			
			foreach ( $data as $day ) {
				
				$months[ $day['month'] ]['sessions']  = ( isset( $months[ $day['month'] ]['sessions'] ) ? $months[ $day['month'] ]['sessions'] : 0 ) + $day['sessions'];
				$months[ $day['month'] ]['users']     = ( isset( $months[ $day['month'] ]['users'] ) ? $months[ $day['month'] ]['users'] : 0 ) + $day['users'];
				$months[ $day['month'] ]['pageviews'] = ( isset( $months[ $day['month'] ]['pageviews'] ) ? $months[ $day['month'] ]['pageviews'] : 0 ) + $day['pageviews'];
				$months[ $day['month'] ]['month']     = $day['month'];
				$months[ $day['month'] ]['year']      = $day['year'];
				
				//$months[$day['month']][] = $months_data;
				
			}
			$gapi_access_token = get_site_option( 'gapi_access_token' );
			?>
            <!--                <div class="gapi_authorization_wrp">-->
            <!--                    <a class="gapi_disconnect">Disconnect</a>-->
            <!--                </div>-->
        </div>
		<?php
		
	}
	
	/**
	 *  Ajax handler for disconnect analytics account
	 */
	public function ns_gapi_disconnect() {
		$access_code  = get_site_option( 'gapi_access_code' );
		$access_token = get_site_option( 'gapi_access_token' );
		if ( $access_code || $access_token ) {
			delete_site_option( 'gapi_access_code' );
			delete_site_option( 'gapi_access_token' );
		}
		wp_send_json_success( 'disconnected' );
	}
	
	/**
	 *  Ajax handler for save GAPI access code
	 */
	public function ns_save_gapi_access_code() {
		
		if ( isset( $_POST['access_code'] ) ) {
			
			$access_code = $_POST['access_code'];
			
			update_site_option( 'gapi_access_code', $access_code );
			
			$this->client->authenticate( $access_code );
			
			$access_token = $this->client->getAccessToken();
			
			if ( ! empty( $access_token ) ) {
				update_site_option( 'gapi_access_token', $access_token );
			}
			wp_send_json_success( 'success' );
		} else {
			wp_send_json_error( 'error' );
		}
	}
	
	function getFirstProfileId() {
		// Get the user's first view (profile) ID.
		
		// Get the list of accounts for the authorized user.
		$accounts = $this->analytics->management_accounts->listManagementAccounts();
		
		if ( count( $accounts->getItems() ) > 0 ) {
			$items          = $accounts->getItems();
			$firstAccountId = $items[0]->getId();
			
			// Get the list of properties for the authorized user.
			$properties = $this->analytics->management_webproperties
				->listManagementWebproperties( $firstAccountId );
			
			if ( count( $properties->getItems() ) > 0 ) {
				$items           = $properties->getItems();
				$firstPropertyId = $items[0]->getId();
				
				// Get the list of views (profiles) for the authorized user.
				$profiles = $this->analytics->management_profiles->listManagementProfiles( $firstAccountId, $firstPropertyId );
				
				if ( count( $profiles->getItems() ) > 0 ) {
					$items = $profiles->getItems();
					
					// Return the first view (profile) ID.
					return $items[0]->getId();
					
				} else {
					echo 'No views (profiles) found for this user.';
					
					return false;
				}
			} else {
				echo 'No properties found for this user.';
				
				return false;
			}
		} else {
			echo 'No accounts found for this user.';
			
			return false;
		}
	}
	
	function getResults( $analytics, $profileId ) {
		// Calls the Core Reporting API and queries for the number of sessions
		// for the last seven days.
		return $analytics->data_ga->get(
			'ga:' . $profileId,
			'300daysAgo',
			'today',
			'ga:visitors' );
	}

//    function printResults($results) {
//        // Parses the response from the Core Reporting API and prints
//        // the profile name and total sessions.
//        if (count($results->getRows()) > 0) {
//
//            // Get the profile name.
//            $profileName = $results->getProfileInfo()->getProfileName();
//
//            // Get the entry for the first entry in the first row.
//            $rows = $results->getRows();
//            $sessions = $rows[0][0];
//
//            // Print the results.
//            print "First view (profile) found: $profileName\n";
//            print "Total sessions: $sessions\n";
//        } else {
//            print "No results found.\n";
//        }
//    }
	
	
	/**
	 * Queries the Analytics Reporting API V4.
	 *
	 * @param service An authorized Analytics Reporting API V4 service object.
	 *
	 * @return The Analytics Reporting API V4 response.
	 */
	function getReport( $analytics ) {
		
		// Replace with your view ID, for example XXXX.
		$VIEW_ID = "164399400";
		
		// Create the DateRange object.
		$dateRange = new Google_Service_AnalyticsReporting_DateRange();
		$dateRange->setStartDate( "7daysAgo" );
		$dateRange->setEndDate( "today" );
		
		// Create the Metrics object.
		$sessions = new Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression( "ga:sessions" );
		$sessions->setAlias( "sessions" );
		
		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId( $VIEW_ID );
		$request->setDateRanges( $dateRange );
		$request->setMetrics( array( $sessions ) );
		
		$body = new Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests( array( $request ) );
		
		return $analytics->reports->batchGet( $body );
	}
	
	/**
	 * Parses and prints the Analytics Reporting API V4 response.
	 *
	 * @param An Analytics Reporting API V4 response.
	 */
	function printResults( $reports ) {
		for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex ++ ) {
			$report           = $reports[ $reportIndex ];
			$header           = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders    = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows             = $report->getData()->getRows();
			
			for ( $rowIndex = 0; $rowIndex < count( $rows ); $rowIndex ++ ) {
				$row        = $rows[ $rowIndex ];
				$dimensions = $row->getDimensions();
				$metrics    = $row->getMetrics();
				for ( $i = 0; $i < count( $dimensionHeaders ) && $i < count( $dimensions ); $i ++ ) {
					print( $dimensionHeaders[ $i ] . ": " . $dimensions[ $i ] . "\n" );
				}
				
				for ( $j = 0; $j < count( $metrics ); $j ++ ) {
					$values = $metrics[ $j ]->getValues();
					for ( $k = 0; $k < count( $values ); $k ++ ) {
						$entry = $metricHeaders[ $k ];
						print( $entry->getName() . ": " . $values[ $k ] . "\n" );
					}
				}
			}
		}
	}
	
	/**
	 * Init google analitics settiongs
	 *
	 * @return void
	 */
	private function init_google_client() {
		//TODO check this...What will happen if this value is null ????
		$this->app_token = get_site_option( 'gapi_access_token' );
		
		$this->client = new Google_Client();
		$this->client->setAccessType( 'offline' );       // offline access.  Will result in a refresh token
		$this->client->setIncludeGrantedScopes( true );   // incremental auth
		$this->client->setClientSecret( NS_Admin_Plugin::clientSecret );
		$this->client->setClientId( NS_Admin_Plugin::clientId );
		$this->client->addScope( Google_Service_Analytics::ANALYTICS_READONLY );
		$this->client->setRedirectUri( NS_Admin_Plugin::redirect );
		$this->client->setScopes( array( 'https://www.googleapis.com/auth/analytics.readonly' ) );
		$this->client->setAccessType( 'offline' );   // Gets us our refreshtoken
		if ( ! empty( $this->app_token ) ) {
			$this->client->setAccessToken( json_encode( $this->app_token ) );
			$this->analytics = new Google_Service_Analytics( $this->client );
			$this->gareports = new Google_Service_AnalyticsReporting( $this->client );
		}
		
	}
	
	/**
	 * Check user permssions and die if he does not have id
	 *
	 * @param string $permission
	 * @param string $message
	 *
	 * @return void
	 */
	private function user_can( $permission, $message = 'You do not have sufficient permissions to access this page.' ) {
		if ( ! current_user_can( $permission ) ) {
			wp_die( __( $message ) );
		}
	}
	
	/**
	 * Get template file
	 *
	 * @param string $template_name
	 * @param array $params
	 *
	 * @return string
	 */
	private function get_template( $template_name, array $params = [] ) {
		$tpl = $this->path . 'lib' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name . '.php';
		if ( $tpl ) {
			return require( $tpl );
		} else {
			$message = "Template {$template_name} not found";
			$this->log( "Template {$template_name} not found" );
			
			return $message;
		}
	}
	
	/*****TASK functionality******/
	//TODO move this to separate class
	/**
	 * Add new task
	 *
	 * @return mixed
	 */
	public function add_task() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		global $wpdb;
		
		$errors = [];
		
		$user_image_url = get_avatar_url( wp_get_current_user() );
		$user_info      = get_userdata( wp_get_current_user() );
		$partner_email  = $user_info->user_email;
		$partner_name   = $this->ns_get_users_name( wp_get_current_user() );
		
		$post_data = [
			'post_title'             => ! empty( $_POST['title'] ) ? $this->sanitize( $_POST, 'title' ) : $errors[] = 'Task title can\'t be empty',
			'post_content'           => ! empty( $_POST['description'] ) ? $this->sanitize( $_POST, 'description' ) : ' ',
			'post_status'            => $this->task_settings->get_default_status(),
			'post_type'              => $this->task_type,
			'_thumbnail_id'          => ! empty( $_POST['_thumbnail_id'] ) ? $_POST['_thumbnail_id'] : '',
			'_task_url'              => ! empty( $_POST['url'] ) && filter_var( $_POST['url'], FILTER_VALIDATE_URL ) ? $this->sanitize( $_POST, 'url' ) : '',
			'_task_priority'         => ! empty( $_POST['priority'] ) ? $this->sanitize( $_POST, 'priority' ) : '',
			'_project_id'            => $this->sanitize( $_POST, '_project_id' ),
			'_reporter_name_client'  => $partner_name,
			'_reporter_image_client' => $user_image_url,
			'_reporter_email_client' => $partner_email,
			'inner'                  => true
		];
		
		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}
		
		//Start transaction
		$wpdb->query( "START TRANSACTION" );
		
		$response = $this->add_task_inner( $post_data );
		if ( ! empty( $response['error'] ) ) {
			$wpdb->query( "ROLLBACK" );
			wp_send_json_error( [ 'errors' => [ ! empty( $response['message'] ) ? $response['message'] : '' ] ] );
		} else {
			$wpdb->query( "COMMIT" );
		}
		
		ob_start();
		$this->get_task_list_html( $this->task_settings->get_request_statuses() );
		wp_send_json_success( [ 'tasks' => ob_get_clean(), 'remote_response_data' => $response ] );
	}
	
	/**
	 * load tasks
	 */
	public function load_next_tasks() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		$statuses = $this->sanitize( $_POST, 'statuses' );
		$statuses = empty( $statuses ) ? [] : explode( ',', $statuses );
		$offset   = $this->sanitize( $_POST, 'offset' );
		
		$params['offset'] = $offset;
		
		ob_start();
		$this->get_task_list_html( $statuses, $params );
		wp_send_json_success( [ 'tasks' => ob_get_clean() ] );
	}
	
	/**
	 * @param array $post_data
	 *
	 * @return array
	 */
	private function add_task_inner( array $post_data ) {
		$post_data['post_title']   = $this->sanitize( $post_data, 'post_title' );
		$post_data['post_content'] = $this->sanitize( $post_data, 'post_content' );
		$post_data['post_status']  = $this->sanitize( $post_data, 'post_status' );
		$post_data['post_type']    = $this->task_type;
		
		$canvas_data                = $post_data['canvas_data'];
		$canvas_data                = unserialize( $canvas_data );
		$canvas_data['canvas_data'] = json_decode( $canvas_data['canvas_data'] );
		
		$post_id = wp_insert_post( $post_data );
		
		$response = [];
		
		if ( $post_id instanceof WP_Error ) {
			$response['error']      = 1;
			$response['error_slug'] = 'task_creation_error';
			$response['message']    = $post_id->get_error_messages();
			
			$this->log( $post_id );
		} else {
			//Set the order the same as post id
			wp_update_post( [
				'ID'         => $post_id,
				'menu_order' => $post_id,
			] );
			
			update_post_meta( $post_id, '_task_url', $this->sanitize( $post_data, '_task_url' ) );
			update_post_meta( $post_id, '_thumbnail_id', $this->sanitize( $post_data, '_thumbnail_id' ) );
			update_post_meta( $post_id, '_task_priority', $this->sanitize( $post_data, '_task_priority' ) );
			update_post_meta( $post_id, '_assignee', $this->sanitize( $post_data, '_assignee' ) );
			update_post_meta( $post_id, '_assignee_name', $this->sanitize( $post_data, '_assignee_name' ) );
			update_post_meta( $post_id, '_reporter_name', $this->sanitize( $post_data, '_reporter_name' ) );
			update_post_meta( $post_id, '_reporter', $this->sanitize( $post_data, '_reporter' ) );
			update_post_meta( $post_id, '_project_id', $this->sanitize( $post_data, '_project_id' ) );
			update_post_meta( $post_id, '_reporter_name_client', $this->sanitize( $post_data, '_reporter_name_client' ) );
			
			if ( ! empty ( $canvas_data ) ) {
				update_post_meta( $post_id, 'canvas_data', $canvas_data );
			}
			
			$post_data['_task_id_client'] = $post_id;
			$post_data['menu_order']      = $post_id;
			if ( empty( $post_data['inner'] ) ) {
				update_post_meta( $post_id, '_task_id_hub', $this->sanitize( $post_data, '_task_id_hub' ) );
				$image_url = $this->sanitize( $post_data, '_thumbnail_url' );
				update_post_meta( $post_id, '_thumbnail_url', $image_url );
				if ( $image_url ) {
					$this->add_post_image_from_url( $post_id, $image_url );
				}
			}
			$response['task_data_response'] = serialize( $post_data );
			
			if ( ! empty( $post_data['inner'] ) ) {
				$post_data['_thumbnail_url'] = get_the_post_thumbnail_url( $post_id, 'full' );
				
				$response['task_data_request'] = $post_data;
				
				$response = $this->add_data_on_remote_site( [ 'task_data' => serialize( $post_data ) ] );
				$this->log( $response );
				
				if ( ! empty( $response['success'] ) ) {
					$task_data_response = unserialize( $response['task_data_response'] );
					update_post_meta( $post_id, '_task_id_hub', $this->sanitize( $task_data_response, '_task_id_hub' ) );
					$response['task_data_response'] = $task_data_response;
					
					$current_user                 = wp_get_current_user();
					$comment_data                 = [
						'post_id'              => $post_id ?: $errors[] = 'Task id can\'t be empty',
						'comment_content'      => ! empty( $post_data['post_content'] ) ? ' just created task <br> <p>' . $this->sanitize( $post_data, 'post_content' ) . '</p>' : ' just created task',
						'comment_author'       => $current_user->user_login,
						'comment_author_email' => $current_user->user_email,
						'comment_author_url'   => home_url() . '/author/' . $current_user->user_login,
						'user_id'              => get_current_user_id(),
						'author_avatar_url'    => get_avatar_url( $current_user->ID ),
						'inner'                => true
					];
					$user_image_url               = get_avatar_url( wp_get_current_user() );
					$comment_data['comment_meta'] = [
						'comment_status_change' => 'changed',
						'comment_client_author' => $current_user->user_login,
						'comment_client_image'  => $user_image_url,
					];
					$descr_comment                = $this->add_comment_inner( $comment_data );
				}
			}
		}
		
		return $response;
	}
	
	/**
	 * Send data to remote
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function add_data_on_remote_site( array $data ) {
		$this->post_data = $data;
		
		$connection_data = $this->prepare_connection_data();
		
		$connection_data = array_merge( $connection_data, $data );
		
		$url = trailingslashit( $connection_data['connection_url'] ) . 'wp-admin/admin-ajax.php';
		
		$remote_site_response = unserialize( $this->request_to_remote_site( $url, $connection_data ) );
		
		$result = [];
		if ( $remote_site_response === false || ( isset( $remote_site_response['error'] ) && $remote_site_response['error'] === 1 ) ) {
			$response = array(
				'error' => 1,
				'body'  => $this->error,
			);
			
			if ( ! empty( $remote_site_response['error'] ) ) {
				$response = array_merge( $response, $remote_site_response );
			}
			
			$result = $response;
		}
		if ( isset( $remote_site_response['success'] ) && $remote_site_response['success'] == 1 ) {
			$result = $remote_site_response;
		}
		
		return $result;
	}
	
	public function add_thread_comment() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		global $wpdb;
		
		$errors = [];
		
		$current_user = wp_get_current_user();
		
		$post_id = $this->sanitize( $_POST, 'post_id' );
		
		$comment_data = [
			'post_id'                => $post_id ?: $errors[] = 'Task id can\'t be empty',
			'comment_content'        => ! empty( $_POST['task_thread_textarea'] ) ? $this->sanitize( $_POST, 'task_thread_textarea' ) : $errors[] = 'Comment can\'t be empty',
			'comment_author'         => $current_user->user_login,
			'comment_author_email'   => $current_user->user_email,
			'comment_author_url'     => home_url() . '/author/' . $current_user->user_login,
			'user_id'                => get_current_user_id(),
			'author_avatar_url'      => get_avatar_url( $current_user->ID ),
			'parent_description_num' => $_POST['parent_description_num'],
			'inner'                  => true
		];
		
		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}
		
		//Start transaction
		$wpdb->query( "START TRANSACTION" );
		
		$response = $this->add_comment_inner( $comment_data );
		
		if ( ! empty( $response['error'] ) ) {
			$wpdb->query( "ROLLBACK" );
			
			wp_send_json_error( [ 'errors' => [ ! empty( $response['message'] ) ? $response['message'] : '' ] ] );
		} else {
			$wpdb->query( "COMMIT" );
		}
		
		ob_start();
		$this->get_comment_list_html( $post_id );
		wp_send_json_success( [
			'comments'             => ob_get_clean(),
			'post_id'              => $post_id,
			'remote_response_data' => $response
		] );
		
	}
	
	/**
	 * Add new comment
	 *
	 * @return void
	 */
	public function add_comment() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		global $wpdb;
		
		$errors = [];
		
		$current_user = wp_get_current_user();
		
		$post_id = $this->sanitize( $_POST, 'post_id' );
		
		$comment_data = [
			'post_id'              => $post_id ?: $errors[] = 'Task id can\'t be empty',
			'comment_content'      => ! empty( $_POST['description'] ) ? $this->sanitize( $_POST, 'description' ) : $errors[] = 'Comment can\'t be empty',
			'comment_author'       => $current_user->user_login,
			'comment_author_email' => $current_user->user_email,
			'comment_author_url'   => home_url() . '/author/' . $current_user->user_login,
			'user_id'              => get_current_user_id(),
			'author_avatar_url'    => get_avatar_url( $current_user->ID ),
			'inner'                => true
		];
		
		
		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}
		
		//Start transaction
		$wpdb->query( "START TRANSACTION" );
		
		$response = $this->add_comment_inner( $comment_data );
		
		if ( ! empty( $response['error'] ) ) {
			$wpdb->query( "ROLLBACK" );
			
			wp_send_json_error( [ 'errors' => [ ! empty( $response['message'] ) ? $response['message'] : '' ] ] );
		} else {
			$wpdb->query( "COMMIT" );
		}
		
		ob_start();
		$this->get_comment_list_html( $post_id );
		wp_send_json_success( [
			'comments'             => ob_get_clean(),
			'post_id'              => $post_id,
			'remote_response_data' => $response
		] );
	}
	
	/**
	 * @param array $comment_data
	 *
	 * @return array
	 */
	private function add_comment_inner( array $comment_data ) {
		if ( ! empty( $comment_data['inner'] ) ) {
			$post = get_post( $comment_data['post_id'] );
			
			if ( ! ( $post instanceof WP_Post ) ) {
				$response['error']      = 1;
				$response['error_slug'] = 'task_search_inner_error';
				$response['message']    = 'Task for the comment was not found on the client site.';
				
				return $response;
			}
		} else {
			$args  = array(
				'meta_key'       => '_task_id_hub',
				'meta_value'     => $this->sanitize( $comment_data, '_comment_post_ID_hub' ),
				'post_type'      => $this->task_type,
				'post_status'    => 'any',
				'posts_per_page' => - 1
			);
			$posts = get_posts( $args );
			$post  = null;
			if ( count( $posts ) ) {
				/**
				 * @var WP_Post $post
				 */
				$post = reset( $posts );
				
			} else {
				$response['error']      = 1;
				$response['error_slug'] = 'task_search_error';
				$response['message']    = 'Task for the comment was not found on the hub site.';
				
				return $response;
			}
		}
		
		$comment_data['comment_post_ID']      = $post->ID;
		$comment_data['comment_author']       = $this->sanitize( $comment_data, 'comment_author' );
		$comment_data['comment_author_email'] = $this->sanitize( $comment_data, 'comment_author_email' );
		$comment_data['comment_author_url']   = $this->sanitize( $comment_data, 'comment_author_url' );
		$allowed_html                         = array(
			'a'  => array(
				'href' => array(),
			),
			'br' => array(),
			"p"  => array(),
		);
		$comment_html                         = wp_kses( $comment_data['comment_content'], $allowed_html );
		$comment_data['comment_content']      = $comment_html;
		$comment_id                           = wp_insert_comment( $comment_data );
		
		$response = [];
		
		if ( ! $comment_id ) {
			$response['error']      = 1;
			$response['error_slug'] = 'comment_insertion_error';
			$response['message']    = 'Comment is not added';
		} else {
			$comment_data['_comment_id_client']      = $comment_id;
			$comment_data['_comment_post_ID_client'] = $post->ID;
			
			$response['comment_data_response'] = serialize( $comment_data );
			
			if ( $comment_data['parent_description_num'] ) {
				update_comment_meta( $comment_id, 'parent_description_num', $comment_data['parent_description_num'] );
				$comment_data['comment_meta'] = [
					'parent_description_num' => $comment_data['parent_description_num']
				];
			}
			
			if ( empty( $comment_data['inner'] ) ) {
				update_comment_meta( $comment_id, '_comment_post_ID_hub', $this->sanitize( $comment_data, '_comment_post_ID_hub' ) );
				update_comment_meta( $comment_id, '_comment_user_id_hub', $this->sanitize( $comment_data, 'user_id' ) );
				update_comment_meta( $comment_id, '_comment_id_hub', $this->sanitize( $comment_data, '_comment_id_hub' ) );
				update_comment_meta( $comment_id, '_author_avatar_url', $this->sanitize( $comment_data, 'author_avatar_url' ) );
			} else {
				$response = $this->add_data_on_remote_site( [ 'comment_data' => serialize( $comment_data ) ] );
				$this->log( $response );
				
				if ( ! empty( $response['success'] ) ) {
					$comment_data_response = unserialize( $response['comment_data_response'] );
					update_comment_meta( $comment_id, '_comment_post_ID_hub', $this->sanitize( $comment_data_response, '_comment_post_ID_hub' ) );
					update_comment_meta( $comment_id, '_comment_id_hub', $this->sanitize( $comment_data_response, '_comment_id_hub' ) );
					$response['comment_data_response'] = $comment_data_response;
				}
			}
		}
		
		return $response;
	}
	
	/**
	 * Save new order
	 *
	 * @return void
	 */
	public function reorder_tasks() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		$newOrder = ! empty( $_POST['newOrder'] ) && is_array( $_POST['newOrder'] ) ? $_POST['newOrder'] : [];
		$item_start = ! empty( $_POST['item_start'] ) ? $_POST['item_start'] : '';
		$item_end = ! empty( $_POST['item_end'] ) ? $_POST['item_end'] : '';
		$item_id = ! empty( $_POST['item_id'] ) ? $_POST['item_id'] : '';
		$prev_item_id = ! empty( $_POST['prev_item_id'] ) ? $_POST['prev_item_id'] : '';
		if ( $newOrder ) {
			foreach ( $newOrder as $no ) {
				$task_id = wp_update_post( [
					'ID'         => $no['id'],
					'menu_order' => $no['order'],
				] );
			}
		}
		$order['inner']      = false;
		$order['order_data'] = $newOrder;
		$order['item_start'] = $item_start;
		$order['item_end'] = $item_end;
		$order['item_id'] = $item_id;
		$order['prev_item_id'] = $prev_item_id;
		$responce            = $this->reorder_tasks_inner( $order );
		wp_send_json_success( $responce );
	}
	
	/**
	 * @param array $post_data
	 *
	 * @return array
	 */
	private function reorder_tasks_inner( $tasks_order ) {
		
		update_site_option( 'tasks_order_option', $tasks_order );
		$tasks_order['inner'] = true;
		$response             = $this->add_data_on_remote_site( [ 'tasks_order' => serialize( $tasks_order ) ] );
		
		return $response;
		
	}
	
	/**
	 * Get tasks ordered by input params
	 *
	 * @return void
	 */
	public function order_tasks() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		$order_by       = ! empty( $_POST['orderBy'] ) ? $_POST['orderBy'] : '';
		$order          = ! empty( $_POST['order'] ) ? $_POST['order'] : '';
		$task_container = ! empty( $_POST['task_container'] ) ? $_POST['task_container'] : '';
		update_site_option( $this->task_settings->complete_order_by_option_key(), $order_by );
		update_site_option( $this->task_settings->complete_order_option_key(), $order );
		
		if ( $order_by == 'modified' ) {
			$params['orderBy'] = $order_by;
		} elseif ( $order_by == 'post_date' ) {
			$params['order_by'] = $order_by;
			$params['orderBy']  = $order_by;
		} elseif ( $order_by == '_task_id_hub' ) {
			$params['orderBy']  = 'meta_value_num';
			$params['meta_key'] = $order_by;
		}
		$params['order'] = $order;
		ob_start();
		if ( $order_by == 'modified' && $task_container == 'complete' ) {
			$this->get_task_list_html( $this->task_settings->get_closed_statuses(), $params );
		} elseif ( $order_by == 'post_date' && $task_container == 'ready_to_test' ) {
			$this->get_task_list_html( $this->task_settings->get_approved_live_statuses(), $params );
		} elseif ( $task_container == 'complete' ) {
			$this->get_task_list_html( $this->task_settings->get_closed_statuses(), $params );
		} elseif ( $task_container == 'ready_to_test' ) {
			$this->get_task_list_html( $this->task_settings->get_approved_live_statuses(), $params );
		} elseif ( $task_container == 'in_progress' ) {
			$this->get_task_list_html( $this->task_settings->get_client_in_progress(), $params );
		}
		
		wp_send_json_success( [ 'tasks' => ob_get_clean() ] );
	}
	
	/**
	 * Get task list html
	 *
	 * @param array $statuses
	 * @param array $params
	 *
	 * @return string
	 */
	private function get_task_list_html( $statuses, $params = [] ) {
		global $tasks_list;
		$tasks_list             = $this->get_task_list( $statuses, $params );
		$tasks_list['statuses'] = $statuses;
		
		return $this->get_template( 'client-task-list' );
	}
	
	/**
	 * Get tasks list
	 *
	 * @param array $statuses
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_task_list( $statuses, $params = [] ) {
		if ( ! is_array( $statuses ) ) {
			$statuses = [ $statuses ];
		}
		$args = [
			'post_type'      => $this->task_type,
			'post_status'    => $statuses,
			'offset'         => 0,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => - 1,
//                'posts_per_page' => $this->per_page,
		];
		if ( ! empty( $params['limit'] ) && $params['limit'] <= 100 ) {
			$args['posts_per_page'] = $params['limit'];
		}
		
		if ( ! empty( $params['offset'] ) ) {
			$args['offset'] = $params['offset'];
		}

//			$args['orderby'] = !empty($params['orderBy']) ? $params['orderBy']: $this->task_settings->default_order_by_value();
//			$args['order'] = !empty($params['order']) ? $params['order']: 'desc';
		$args['meta_key'] = ! empty( $params['meta_key'] ) ? $params['meta_key'] : '';
		
		$args['meta_query'] = [
			'relation' => 'OR',
			[
				'key'     => '_private',
				'value'   => '',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => '_private',
				'value'   => 'true',
				'compare' => '!=',
			]
		];
		
		$query            = new WP_Query( $args );
		$hide_load_button = $query->found_posts < $args['offset'] + $this->per_page;
		wp_reset_query();
		
		return [
			'posts'            => $query->posts,
			'total'            => $query->found_posts,
			'hide_load_button' => $hide_load_button,
			'new_offset'       => ( $args['offset'] + $this->per_page )
		];
	}
	
	/**
	 * GEt tsk order by setting
	 *
	 * @return mixed|string|void
	 */
	private function get_tasks_order_by() {
		$order_by = get_site_option( $this->task_settings->complete_order_by_option_key() );
		if ( empty( $order_by ) ) {
			$order_by = $this->task_settings->default_order_by_value();
		}
		
		return $order_by;
	}
	
	/**
	 * Get task order setting
	 * @return string
	 */
	private function get_tasks_order() {
		$order = get_site_option( $this->task_settings->complete_order_option_key() );
		if ( empty( $order ) ) {
			$order = $this->task_settings->default_order_value();
		}
		
		return $order;
	}
	
	/**
	 * Get comment html
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	private function get_comment_list_html( $post_id ) {
		global $comments_list;
		$comments_list = $this->get_comment_list( $post_id );
		
		return $this->get_template( 'client-comments-list' );
	}
	
	/**
	 * Get comments list
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	private function get_comment_list( $post_id ) {
		$args = [
			'post_id' => $post_id,
			'status'  => 'approve',
			'orderby' => 'ID',
			'order'   => 'ASC',
		];
		
		return get_comments( $args );
	}
	
	/**
	 * Wordpress image uploader
	 *
	 * @return void
	 */
	public function image_uploader() {
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_media' ) );
	}
	
	/**
	 * Filter images
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function filter_media( $query ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			$query['author'] = get_current_user_id();
		}
		
		return $query;
	}
	
	public function ns_task_delete() {
		
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		
		$task_data_status['inner']           = true;
		$task_data_status['task_hub_id']     = $this->sanitize( $_POST, 'task_hub_id' );
		$task_data_status['_task_id_client'] = (int) $this->sanitize( $_POST, 'client_task_id' );
		
		$response = $this->delete_task_inner( $task_data_status );
		
		if ( ! empty( $response['error'] ) ) {
			
			wp_send_json_error( [ 'errors' => [ ! empty( $response['message'] ) ? $response['message'] : '' ] ] );
		} else {
			wp_send_json_success( [ 'remote_response_data' => $response ] );
		}
		
	}
	
	/**
	 * @param array $task_data_status
	 *
	 * @return array
	 */
	private function delete_task_inner( array $task_data_status ) {
		$task = get_post( $task_data_status['_task_id_client'] );
		
		if ( ! ( $task instanceof WP_Post ) ) {
			if ( ! empty( $task_data_status['inner'] ) ) {
				$response['error']      = 1;
				$response['error_slug'] = 'task_search_inner_error';
				$response['message']    = 'Task for the changing status was not found on the client site.';
				
				return $response;
			} else {
				wp_send_json_error( [ 'errors' => [ 'Task has not found' ] ] );
			}
		}
		
		$task_data_status['ID']           = $task->ID;
		$task_data_status['_task_id_hub'] = get_post_meta( $task->ID, '_task_id_hub', true );
		
		
		$post_id = wp_update_post( $task_data_status, true );
		
		$response = [];
		
		if ( $post_id instanceof WP_Error ) {
			$response['error']      = 1;
			$response['error_slug'] = 'task_status_delete_error';
			$response['message']    = $post_id->get_error_messages();
		} else {
			
			if ( ! empty( $task_data_status['inner'] ) ) {
				
				$response = $this->add_data_on_remote_site( [ 'task_data_delete' => serialize( $task_data_status ) ] );
				$this->log( $response );
				wp_delete_post( $task->ID, true );
			}
		}
		
		return $response;
	}
	
	
	/*
		 * WP Dashicons list generated by Misha Malashevych 21.03.2018
		 */
	public function wp_list_dashicons() {
		//<!-- admin menu -->
		$di = array();
		/*
			<!-- admin menu -->
					   <div data-code="f333" class="dashicons dashicons-menu"></div>
					   <div data-code="f319" class="dashicons dashicons-admin-site"></div>
					   <div data-code="f226" class="dashicons dashicons-dashboard"></div>
					   <div data-code="f109" class="dashicons dashicons-admin-post"></div>
					   <div data-code="f104" class="dashicons dashicons-admin-media"></div>
					   <div data-code="f103" class="dashicons dashicons-admin-links"></div>
					   <div data-code="f105" class="dashicons dashicons-admin-page"></div>
					   <div data-code="f101" class="dashicons dashicons-admin-comments"></div>
					   <div data-code="f100" class="dashicons dashicons-admin-appearance"></div>
					   <div data-code="f106" class="dashicons dashicons-admin-plugins"></div>
					   <div data-code="f110" class="dashicons dashicons-admin-users"></div>
					   <div data-code="f107" class="dashicons dashicons-admin-tools"></div>
					   <div data-code="f108" class="dashicons dashicons-admin-settings"></div>
					   <div data-code="f112" class="dashicons dashicons-admin-network"></div>
					   <div data-code="f102" class="dashicons dashicons-admin-home"></div>
					   <div data-code="f111" class="dashicons dashicons-admin-generic"></div>
					   <div data-code="f148" class="dashicons dashicons-admin-collapse"></div>
			*/
		$di[] = 'menu';
		$di[] = 'admin-site';
		$di[] = 'dashboard';
		$di[] = 'admin-post';
		$di[] = 'admin-media';
		$di[] = 'admin-links';
		$di[] = 'admin-page';
		$di[] = 'admin-comments';
		$di[] = 'admin-appearance';
		$di[] = 'admin-plugins';
		$di[] = 'admin-users';
		$di[] = 'admin-tools';
		$di[] = 'admin-settings';
		$di[] = 'admin-network';
		$di[] = 'admin-home';
		$di[] = 'admin-generic';
		$di[] = 'admin-collapse';
		
		/*
					   <!-- welcome screen -->
					   <div data-code="f119" class="dashicons dashicons-welcome-write-blog"></div>
					   <!--<div data-code="f119" class="dashicons dashicons-welcome-edit-page"></div> Duplicate -->
					   <div data-code="f133" class="dashicons dashicons-welcome-add-page"></div>
					   <div data-code="f115" class="dashicons dashicons-welcome-view-site"></div>
					   <div data-code="f116" class="dashicons dashicons-welcome-widgets-menus"></div>
					   <div data-code="f117" class="dashicons dashicons-welcome-comments"></div>
					   <div data-code="f118" class="dashicons dashicons-welcome-learn-more"></div>
			*/
		$di[] = 'welcome-write-blog';
		$di[] = 'welcome-add-page';
		$di[] = 'welcome-view-site';
		$di[] = 'welcome-widgets-menus';
		$di[] = 'welcome-comments';
		$di[] = 'welcome-learn-more';
		
		/*
					   <!-- post formats -->
					   <!--<div data-code="f109" class="dashicons dashicons-format-standard"></div> Duplicate -->
					   <div data-code="f123" class="dashicons dashicons-format-aside"></div>
					   <div data-code="f128" class="dashicons dashicons-format-image"></div>
					   <div data-code="f161" class="dashicons dashicons-format-gallery"></div>
					   <div data-code="f126" class="dashicons dashicons-format-video"></div>
					   <div data-code="f130" class="dashicons dashicons-format-status"></div>
					   <div data-code="f122" class="dashicons dashicons-format-quote"></div>
					   <!--<div data-code="f103" class="dashicons dashicons-format-links"></div> Duplicate -->
					   <div data-code="f125" class="dashicons dashicons-format-chat"></div>
					   <div data-code="f127" class="dashicons dashicons-format-audio"></div>
					   <div data-code="f306" class="dashicons dashicons-camera"></div>
					   <div data-code="f232" class="dashicons dashicons-images-alt"></div>
					   <div data-code="f233" class="dashicons dashicons-images-alt2"></div>
					   <div data-code="f234" class="dashicons dashicons-video-alt"></div>
					   <div data-code="f235" class="dashicons dashicons-video-alt2"></div>
					   <div data-code="f236" class="dashicons dashicons-video-alt3"></div>
			*/
		$di[] = 'format-aside';
		$di[] = 'format-image';
		$di[] = 'format-gallery';
		$di[] = 'format-video';
		$di[] = 'format-status';
		$di[] = 'format-quote';
		$di[] = 'format-chat';
		$di[] = 'format-audio';
		$di[] = 'camera';
		$di[] = 'images-alt';
		$di[] = 'images-alt2';
		$di[] = 'video-alt';
		$di[] = 'video-alt2';
		$di[] = 'video-alt3';
		
		/*
					   <!-- media -->
					   <div data-code="f501" class="dashicons dashicons-media-archive"></div>
					   <div data-code="f500" class="dashicons dashicons-media-audio"></div>
					   <div data-code="f499" class="dashicons dashicons-media-code"></div>
					   <div data-code="f498" class="dashicons dashicons-media-default"></div>
					   <div data-code="f497" class="dashicons dashicons-media-document"></div>
					   <div data-code="f496" class="dashicons dashicons-media-interactive"></div>
					   <div data-code="f495" class="dashicons dashicons-media-spreadsheet"></div>
					   <div data-code="f491" class="dashicons dashicons-media-text"></div>
					   <div data-code="f490" class="dashicons dashicons-media-video"></div>
					   <div data-code="f492" class="dashicons dashicons-playlist-audio"></div>
					   <div data-code="f493" class="dashicons dashicons-playlist-video"></div>
			*/
		$di[] = 'media-archive';
		$di[] = 'media-audio';
		$di[] = 'media-code';
		$di[] = 'media-default';
		$di[] = 'media-document';
		$di[] = 'media-interactive';
		$di[] = 'media-spreadsheet';
		$di[] = 'media-text';
		$di[] = 'media-video';
		$di[] = 'playlist-audio';
		$di[] = 'playlist-video';
		/*
					   <!-- image editing -->
					   <div data-code="f165" class="dashicons dashicons-image-crop"></div>
					   <div data-code="f166" class="dashicons dashicons-image-rotate-left"></div>
					   <div data-code="f167" class="dashicons dashicons-image-rotate-right"></div>
					   <div data-code="f168" class="dashicons dashicons-image-flip-vertical"></div>
					   <div data-code="f169" class="dashicons dashicons-image-flip-horizontal"></div>
					   <div data-code="f171" class="dashicons dashicons-undo"></div>
					   <div data-code="f172" class="dashicons dashicons-redo"></div>
			*/
		$di[] = 'image-crop';
		$di[] = 'image-rotate-left';
		$di[] = 'image-rotate-right';
		$di[] = 'image-flip-vertical';
		$di[] = 'image-flip-horizontal';
		$di[] = 'undo';
		$di[] = 'redo';
		
		/*
					   <!-- tinymce -->
					   <div data-code="f200" class="dashicons dashicons-editor-bold"></div>
					   <div data-code="f201" class="dashicons dashicons-editor-italic"></div>
					   <div data-code="f203" class="dashicons dashicons-editor-ul"></div>
					   <div data-code="f204" class="dashicons dashicons-editor-ol"></div>
					   <div data-code="f205" class="dashicons dashicons-editor-quote"></div>
					   <div data-code="f206" class="dashicons dashicons-editor-alignleft"></div>
					   <div data-code="f207" class="dashicons dashicons-editor-aligncenter"></div>
					   <div data-code="f208" class="dashicons dashicons-editor-alignright"></div>
					   <div data-code="f209" class="dashicons dashicons-editor-insertmore"></div>
					   <div data-code="f210" class="dashicons dashicons-editor-spellcheck"></div>
					   <!-- <div data-code="f211" class="dashicons dashicons-editor-distractionfree"></div> Duplicate -->
					   <div data-code="f211" class="dashicons dashicons-editor-expand"></div>
					   <div data-code="f506" class="dashicons dashicons-editor-contract"></div>
					   <div data-code="f212" class="dashicons dashicons-editor-kitchensink"></div>
					   <div data-code="f213" class="dashicons dashicons-editor-underline"></div>
					   <div data-code="f214" class="dashicons dashicons-editor-justify"></div>
					   <div data-code="f215" class="dashicons dashicons-editor-textcolor"></div>
					   <div data-code="f216" class="dashicons dashicons-editor-paste-word"></div>
					   <div data-code="f217" class="dashicons dashicons-editor-paste-text"></div>
					   <div data-code="f218" class="dashicons dashicons-editor-removeformatting"></div>
					   <div data-code="f219" class="dashicons dashicons-editor-video"></div>
					   <div data-code="f220" class="dashicons dashicons-editor-customchar"></div>
					   <div data-code="f221" class="dashicons dashicons-editor-outdent"></div>
					   <div data-code="f222" class="dashicons dashicons-editor-indent"></div>
					   <div data-code="f223" class="dashicons dashicons-editor-help"></div>
					   <div data-code="f224" class="dashicons dashicons-editor-strikethrough"></div>
					   <div data-code="f225" class="dashicons dashicons-editor-unlink"></div>
					   <div data-code="f320" class="dashicons dashicons-editor-rtl"></div>
					   <div data-code="f474" class="dashicons dashicons-editor-break"></div>
					   <div data-code="f475" class="dashicons dashicons-editor-code"></div>
					   <div data-code="f476" class="dashicons dashicons-editor-paragraph"></div>
			  */
		
		$di[] = 'editor-bold';
		$di[] = 'editor-italic';
		$di[] = 'editor-ul';
		$di[] = 'editor-ol';
		$di[] = 'editor-quote';
		$di[] = 'editor-alignleft';
		$di[] = 'editor-aligncenter';
		$di[] = 'editor-alignright';
		$di[] = 'editor-insertmore';
		$di[] = 'editor-spellcheck';
		$di[] = 'editor-distractionfree';
		$di[] = 'editor-expand';
		$di[] = 'editor-contract';
		$di[] = 'editor-kitchensink';
		$di[] = 'editor-underline';
		$di[] = 'editor-justify';
		$di[] = 'editor-textcolor';
		$di[] = 'editor-paste-word';
		$di[] = 'editor-removeformatting';
		$di[] = 'editor-video';
		$di[] = 'editor-customchar';
		$di[] = 'editor-outdent';
		$di[] = 'editor-indent';
		$di[] = 'editor-help';
		$di[] = 'editor-strikethrough';
		$di[] = 'editor-unlink';
		//$di[] = 'editor-link';		- Editor link doesn't exist as a separate icon. use admin-links
		$di[] = 'editor-rtl';
		$di[] = 'editor-break';
		$di[] = 'editor-code';
		$di[] = 'editor-paragraph';
		/*
					  <!-- posts -->
					  <div data-code="f135" class="dashicons dashicons-align-left"></div>
					  <div data-code="f136" class="dashicons dashicons-align-right"></div>
					  <div data-code="f134" class="dashicons dashicons-align-center"></div>
					  <div data-code="f138" class="dashicons dashicons-align-none"></div>
					  <div data-code="f160" class="dashicons dashicons-lock"></div>
					  <div data-code="f145" class="dashicons dashicons-calendar"></div>
					  <div data-code="f177" class="dashicons dashicons-visibility"></div>
					  <div data-code="f173" class="dashicons dashicons-post-status"></div>
					  <div data-code="f464" class="dashicons dashicons-edit"></div>
					  <div data-code="f182" class="dashicons dashicons-trash"></div>
			 */
		$di[] = 'align-left';
		$di[] = 'align-right';
		$di[] = 'align-center';
		$di[] = 'align-none';
		$di[] = 'lock';
		$di[] = 'calendar';
		$di[] = 'calendar-alt';
		$di[] = 'visibility';
		$di[] = 'post-status';
		$di[] = 'edit';
		$di[] = 'trash';
		/*
					 <!-- sorting -->
					 <div data-code="f504" class="dashicons dashicons-external"></div>
					 <div data-code="f142" class="dashicons dashicons-arrow-up"></div>
					 <div data-code="f140" class="dashicons dashicons-arrow-down"></div>
					 <div data-code="f139" class="dashicons dashicons-arrow-right"></div>
					 <div data-code="f141" class="dashicons dashicons-arrow-left"></div>
					 <div data-code="f342" class="dashicons dashicons-arrow-up-alt"></div>
					 <div data-code="f346" class="dashicons dashicons-arrow-down-alt"></div>
					 <div data-code="f344" class="dashicons dashicons-arrow-right-alt"></div>
					 <div data-code="f340" class="dashicons dashicons-arrow-left-alt"></div>
					 <div data-code="f343" class="dashicons dashicons-arrow-up-alt2"></div>
					 <div data-code="f347" class="dashicons dashicons-arrow-down-alt2"></div>
					 <div data-code="f345" class="dashicons dashicons-arrow-right-alt2"></div>
					 <div data-code="f341" class="dashicons dashicons-arrow-left-alt2"></div>
					 <div data-code="f156" class="dashicons dashicons-sort"></div>
					 <div data-code="f229" class="dashicons dashicons-leftright"></div>
					 <div data-code="f503" class="dashicons dashicons-randomize"></div>
					 <div data-code="f163" class="dashicons dashicons-list-view"></div>
					 <div data-code="f164" class="dashicons dashicons-exerpt-view"></div>
			*/
		
		$di[] = 'external';
		$di[] = 'arrow-up';
		$di[] = 'arrow-down';
		$di[] = 'arrow-right';
		$di[] = 'arrow-left';
		$di[] = 'arrow-up-alt';
		$di[] = 'arrow-down-alt';
		$di[] = 'arrow-right-alt';
		$di[] = 'arrow-left-alt';
		$di[] = 'arrow-up-alt2';
		$di[] = 'arrow-down-alt2';
		$di[] = 'arrow-right-alt2';
		$di[] = 'arrow-left-alt2';
		$di[] = 'sort';
		$di[] = 'leftright';
		$di[] = 'randomize';
		$di[] = 'list-view';
		$di[] = 'exerpt-view';  // sic	- not been fixed yet
		$di[] = 'grid-view';
		
		
		/*
					 <!-- social -->
					 <div data-code="f237" class="dashicons dashicons-share"></div>
					 <div data-code="f240" class="dashicons dashicons-share-alt"></div>
					 <div data-code="f242" class="dashicons dashicons-share-alt2"></div>
					 <div data-code="f301" class="dashicons dashicons-twitter"></div>
					 <div data-code="f303" class="dashicons dashicons-rss"></div>
					 <div data-code="f465" class="dashicons dashicons-email"></div>
					 <div data-code="f466" class="dashicons dashicons-email-alt"></div>
					 <div data-code="f304" class="dashicons dashicons-facebook"></div>
					 <div data-code="f305" class="dashicons dashicons-facebook-alt"></div>
					 <div data-code="f462" class="dashicons dashicons-googleplus"></div>
					 <div data-code="f325" class="dashicons dashicons-networking"></div>
		   */
		$di[] = 'share';
		$di[] = 'share1';
		$di[] = 'share-alt';
		$di[] = 'share-alt2';
		$di[] = 'twitter';
		$di[] = 'rss';
		$di[] = 'email';
		$di[] = 'email-alt';
		$di[] = 'facebook';
		$di[] = 'facebook-alt';
		$di[] = 'googleplus';
		$di[] = 'networking';
		
		/*
					  <!-- WPorg specific icons: Jobs, Profiles, WordCamps -->
					  <div data-code="f308" class="dashicons dashicons-hammer"></div>
					  <div data-code="f309" class="dashicons dashicons-art"></div>
					  <div data-code="f310" class="dashicons dashicons-migrate"></div>
					  <div data-code="f311" class="dashicons dashicons-performance"></div>
					  <div data-code="f483" class="dashicons dashicons-universal-access"></div>
					  <div data-code="f507" class="dashicons dashicons-universal-access-alt"></div>
					  <div data-code="f486" class="dashicons dashicons-tickets"></div>
					  <div data-code="f484" class="dashicons dashicons-nametag"></div>
					  <div data-code="f481" class="dashicons dashicons-clipboard"></div>
					  <div data-code="f487" class="dashicons dashicons-heart"></div>
					  <div data-code="f488" class="dashicons dashicons-megaphone"></div>
					  <div data-code="f489" class="dashicons dashicons-schedule"></div>
			*/
		$di[] = 'hammer';
		$di[] = 'art';
		$di[] = 'migrate';
		$di[] = 'performance';
		$di[] = 'universal-access';
		$di[] = 'universal-access-alt';
		$di[] = 'tickets';
		$di[] = 'nametag';
		$di[] = 'clipboard';
		$di[] = 'heart';
		$di[] = 'megaphone';
		$di[] = 'schedule';
		/*
					  <!-- internal/products -->
					  <div data-code="f120" class="dashicons dashicons-wordpress"></div>
					  <div data-code="f324" class="dashicons dashicons-wordpress-alt"></div>
					  <div data-code="f157" class="dashicons dashicons-pressthis"></div>
					  <div data-code="f463" class="dashicons dashicons-update"></div>
					  <div data-code="f180" class="dashicons dashicons-screenoptions"></div>
					  <div data-code="f348" class="dashicons dashicons-info"></div>
					  <div data-code="f174" class="dashicons dashicons-cart"></div>
					  <div data-code="f175" class="dashicons dashicons-feedback"></div>
					  <div data-code="f176" class="dashicons dashicons-cloud"></div>
					  <div data-code="f326" class="dashicons dashicons-translation"></div>
			*/
		$di[] = 'wordpress';
		$di[] = 'wordpress-alt';
		$di[] = 'pressthis';
		$di[] = 'update';
		$di[] = 'screenoptions';
		$di[] = 'info';
		$di[] = 'cart';
		$di[] = 'feedback';
		$di[] = 'cloud';
		$di[] = 'translation';
		/*
					  <!-- taxonomies -->
					  <div data-code="f323" class="dashicons dashicons-tag"></div>
					  <div data-code="f318" class="dashicons dashicons-category"></div>
			*/
		$di[] = 'tag';
		$di[] = 'category';
		/*
					  <!-- widgets -->
					  <div data-code="f480" class="dashicons dashicons-archive"></div>
					  <div data-code="f479" class="dashicons dashicons-tagcloud"></div>
					  <div data-code="f478" class="dashicons dashicons-text"></div>
			*/
		$di[] = 'archive';
		$di[] = 'tagcloud';
		$di[] = 'text';
		/*
					  <!-- alerts/notifications/flags -->
					  <div data-code="f147" class="dashicons dashicons-yes"></div>
					  <div data-code="f158" class="dashicons dashicons-no"></div>
					  <div data-code="f335" class="dashicons dashicons-no-alt"></div>
					  <div data-code="f132" class="dashicons dashicons-plus"></div>
					  <div data-code="f502" class="dashicons dashicons-plus-alt"></div>
					  <div data-code="f460" class="dashicons dashicons-minus"></div>
					  <div data-code="f153" class="dashicons dashicons-dismiss"></div>
					  <div data-code="f159" class="dashicons dashicons-marker"></div>
					  <div data-code="f155" class="dashicons dashicons-star-filled"></div>
					  <div data-code="f459" class="dashicons dashicons-star-half"></div>
					  <div data-code="f154" class="dashicons dashicons-star-empty"></div>
					  <div data-code="f227" class="dashicons dashicons-flag"></div>
			*/
		$di[] = 'yes';
		$di[] = 'no';
		$di[] = 'no-alt';
		$di[] = 'plus';
		$di[] = 'plus-alt';
		$di[] = 'minus';
		$di[] = 'dismiss';
		$di[] = 'marker';
		$di[] = 'star-filled';
		$di[] = 'star-half';
		$di[] = 'star-empty';
		$di[] = 'flag';
		/*
					  <!-- misc/cpt -->
					  <div data-code="f230" class="dashicons dashicons-location"></div>
					  <div data-code="f231" class="dashicons dashicons-location-alt"></div>
					  <div data-code="f178" class="dashicons dashicons-vault"></div>
					  <div data-code="f332" class="dashicons dashicons-shield"></div>
					  <div data-code="f334" class="dashicons dashicons-shield-alt"></div>
					  <div data-code="f468" class="dashicons dashicons-sos"></div>
					  <div data-code="f179" class="dashicons dashicons-search"></div>
					  <div data-code="f181" class="dashicons dashicons-slides"></div>
					  <div data-code="f183" class="dashicons dashicons-analytics"></div>
					  <div data-code="f184" class="dashicons dashicons-chart-pie"></div>
					  <div data-code="f185" class="dashicons dashicons-chart-bar"></div>
					  <div data-code="f238" class="dashicons dashicons-chart-line"></div>
					  <div data-code="f239" class="dashicons dashicons-chart-area"></div>
					  <div data-code="f307" class="dashicons dashicons-groups"></div>
					  <div data-code="f338" class="dashicons dashicons-businessman"></div>
					  <div data-code="f336" class="dashicons dashicons-id"></div>
					  <div data-code="f337" class="dashicons dashicons-id-alt"></div>
					  <div data-code="f312" class="dashicons dashicons-products"></div>
					  <div data-code="f313" class="dashicons dashicons-awards"></div>
					  <div data-code="f314" class="dashicons dashicons-forms"></div>
					  <div data-code="f473" class="dashicons dashicons-testimonial"></div>
					  <div data-code="f322" class="dashicons dashicons-portfolio"></div>
					  <div data-code="f330" class="dashicons dashicons-book"></div>
					  <div data-code="f331" class="dashicons dashicons-book-alt"></div>
					  <div data-code="f316" class="dashicons dashicons-download"></div>
					  <div data-code="f317" class="dashicons dashicons-upload"></div>
					  <div data-code="f321" class="dashicons dashicons-backup"></div>
					  <div data-code="f469" class="dashicons dashicons-clock"></div>
					  <div data-code="f339" class="dashicons dashicons-lightbulb"></div>
					  <div data-code="f482" class="dashicons dashicons-microphone"></div>
					  <div data-code="f472" class="dashicons dashicons-desktop"></div>
					  <div data-code="f471" class="dashicons dashicons-tablet"></div>
					  <div data-code="f470" class="dashicons dashicons-smartphone"></div>
					  <div data-code="f328" class="dashicons dashicons-smiley"></div>
				  </div>
		   */
		$di[] = 'location';
		$di[] = 'location-alt';
		$di[] = 'vault';
		$di[] = 'shield';
		$di[] = 'shield-alt';
		$di[] = 'sos';
		$di[] = 'search';
		$di[] = 'slides';
		$di[] = 'analytics';
		$di[] = 'chart-pie';
		$di[] = 'chart-bar';
		$di[] = 'chart-line';
		$di[] = 'chart-area';
		$di[] = 'groups';
		$di[] = 'businessman';
		$di[] = 'id';
		$di[] = 'id-alt';
		$di[] = 'products';
		$di[] = 'awards';
		$di[] = 'forms';
		$di[] = 'testimonial';
		$di[] = 'portfolio';
		$di[] = 'book';
		$di[] = 'book-alt';
		$di[] = 'download';
		$di[] = 'upload';
		$di[] = 'backup';
		$di[] = 'clock';
		$di[] = 'lightbulb';
		$di[] = 'microphone';
		$di[] = 'desktop';
		$di[] = 'tablet';
		$di[] = 'smartphone';
		$di[] = 'smiley';
		
		// New in WordPress 4.1
		$di[] = "controls-play";
		$di[] = "controls-pause";
		$di[] = "controls-forward";
		$di[] = "controls-skipforward";
		$di[] = "controls-back";
		$di[] = "controls-skipback";
		$di[] = "controls-repeat";
		$di[] = "controls-volumeon";
		$di[] = "controls-volumeoff";
		$di[] = "align-left";
		$di[] = "align-right";
		$di[] = "align-center";
		$di[] = "align-none";
		$di[] = "phone";
		$di[] = "building";
		$di[] = "store";
		$di[] = "album";
		$di[] = "palmtree";
		$di[] = "tickets-alt";
		$di[] = "money";
		
		$di[] = 'index-card';
		$di[] = 'carrot';
		// New in WordPress up to 4.7
		$di[] = "filter";
		$di[] = "admin-customizer";
		$di[] = "admin-multisite";
		$di[] = "image-rotate";
		$di[] = "image-filter";
		$di[] = "editor-table";
		$di[] = "unlock";
		$di[] = "hidden";
		$di[] = "sticky";
		$di[] = "excerpt-view";  // Now corrected
		$di[] = "move";
		$di[] = "plus-alt2";
		$di[] = "warning";
		$di[] = "laptop";
		$di[] = "thumbs-up";
		$di[] = "thumbs-down";
		$di[] = "layout";
		$di[] = "paperclip";
		
		// New in WordPress 4.x	 - NO, this is an SVG icon...
		//$di[] = "button";
		return ( $di );
	}
	
	public function set_menu_icon_and_name() {
		
		$menu_icon_data = get_site_option( 'ns_wp_menu_icon_data' );
		
		if ( ! empty( $menu_icon_data ) ) {
			$menu_icon_data[ $_POST['SelectedMenuItem'] ] = array(
				'icon' => $_POST['SelectedClass'],
				'name' => $_POST['menu_name']
			);
		} else {
			$menu_icon_data                               = [];
			$menu_icon_data[ $_POST['SelectedMenuItem'] ] = array(
				'icon' => $_POST['SelectedClass'],
				'name' => $_POST['menu_name']
			);
		}
//            print_r($menu_icon_data);
//			update_site_option( 'ns_wp_menu_icon_data', '' );
//			die();
		if ( ! empty( $menu_icon_data ) ) {
			update_site_option( 'ns_wp_menu_icon_data', $menu_icon_data );
			wp_send_json_success( 'menu saved' );
		} else {
			wp_send_json_error( 'not selected icon' );
		}
	}
	
	public function remove_connection_data() {
		
		if ( delete_site_option( static::MANAGER_SETTINGS_KEY ) ) {
			update_site_option( 'connection_to_hub_status', 'disconnected' );
			wp_send_json_success( 'connection disabled' );
		} else {
			wp_send_json_error( 'can not delete connection data' );
		}
	}
	
	public function generate_connection_key() {
		$key_symbols    = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/*-&%$#@!';
		$connection_key = '';
		
		for ( $i = 0; $i < 32; $i ++ ) {
			$connection_key .= substr( $key_symbols, wp_rand( 0, strlen( $key_symbols ) - 1 ), 1 );
		}
		
		return $connection_key;
	}
	
	/**
	 * Use it for log
	 *
	 * @param $data
	 */
	private function log( $data ) {
		error_log( 'Client ' . print_r( $data, true ) );;
	}
	
	/**
	 * Sanitize data for prevent exploits
	 *
	 * @param array $data
	 * @param string $key
	 *
	 * @return string
	 */
	private function sanitize( array $data, $key ) {
		return isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
	}
	
	/**
	 * Add image for the post from the url
	 *
	 * @param $post_id
	 * @param $image_url
	 *
	 * @return void
	 */
	private function add_post_image_from_url( $post_id, $image_url ) {
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents( $image_url );
		$filename   = basename( $image_url );
		
		$file = ( wp_mkdir_p( $upload_dir['path'] ) ? $upload_dir['path'] : $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . $filename;
		
		file_put_contents( $file, $image_data );
		
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment  = [
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		];
		$attach_id   = wp_insert_attachment( $attachment, $file, $post_id );
		
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		set_post_thumbnail( $post_id, $attach_id );
	}
	
	/**
	 * @return mixed
	 */
	public function get_projects() {
		$timeline_meta_data = get_site_option( 'received_data' );
		
		return unserialize( $timeline_meta_data['projects_data'] );
	}
	
	public function task_approve() {
		$approved_on = $_POST['approved_environment'];
		$post_id     = $_POST['task_id'];
		
		if ( $approved_on == 'live' ) {
			update_post_meta( $post_id, 'task_live_approved', $approved_on );
		} else {
			update_post_meta( $post_id, 'task_staging_approved', $approved_on );
		}
		
		wp_send_json_success( 'fire' );
	}
	
	//get user name
	private function ns_get_users_name( $user_id = null ) {
		$user_info = $user_id instanceof WP_User
			? $user_id
			: ( $user_id
				? new WP_User( $user_id )
				: wp_get_current_user() );
		if ( $user_info->first_name ) {
			if ( $user_info->last_name ) {
				return $user_info->first_name . ' ' . $user_info->last_name;
			}
			
			return $user_info->first_name;
		}
		
		return $user_info->display_name;
	}
	
	public function makeLinks( $str ) {
		$reg_exUrl     = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		$urls          = array();
		$urlsToReplace = array();
		if ( preg_match_all( $reg_exUrl, $str, $urls ) ) {
			$numOfMatches       = count( $urls[0] );
			$numOfUrlsToReplace = 0;
			for ( $i = 0; $i < $numOfMatches; $i ++ ) {
				$alreadyAdded       = false;
				$numOfUrlsToReplace = count( $urlsToReplace );
				for ( $j = 0; $j < $numOfUrlsToReplace; $j ++ ) {
					if ( $urlsToReplace[ $j ] == $urls[0][ $i ] ) {
						$alreadyAdded = true;
					}
				}
				if ( ! $alreadyAdded ) {
					array_push( $urlsToReplace, $urls[0][ $i ] );
				}
			}
			$numOfUrlsToReplace = count( $urlsToReplace );
			for ( $i = 0; $i < $numOfUrlsToReplace; $i ++ ) {
				$str = str_replace( $urlsToReplace[ $i ], "<a target='_blank' href=\"" . $urlsToReplace[ $i ] . "\">" . $urlsToReplace[ $i ] . "</a> ", $str );
			}
			
			return $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Plugin version version check
	 */
	
	
}

new NS_Admin_Plugin( new NS_Client_Task_Settings );
