<?php

$all_plugins            = get_plugins();
$all_updated_plugins    = get_plugin_updates();
$plugins_need_to_update = array();
$updated_need_list      = array();
$all_dir_plugins        = $this->get_plugins_filtered( $all_plugins );
$pluginupdates          = get_site_transient( 'update_plugins' );

if ( $all_updated_plugins ) {
	$plugins_needeed_to_update = array();
	foreach ( $all_updated_plugins as $key => $value ) {
		$plugins_needeed_to_update[] = $key;
	}
}
foreach ( $all_plugins as $key => $value ) {
	if ( $plugins_needeed_to_update ) {
		if ( in_array( $key, $plugins_needeed_to_update ) ) {
			$plugins_need_to_update[ $key ] = $value;
		}
	}
}
$all_updates_dirs = $this->get_plugins_filtered( $plugins_need_to_update );
if ( $all_updates_dirs ) {
	foreach ( $all_updates_dirs as $plugin ) {
		$updated_need_list[] = $plugin['text_domain'];
	}
}
?>
<div class="plugin_management_wrp">
    <div class="plugin_management_title section_main_title">
        <h3> <?php echo __( 'Plugin Management', 'ns_admin' ); ?> </h3>
        <div class="gapi_filter_wrp plugin_management_filter">
			<?php
			$active_plugins_count   = 0;
			$inactive_plugins_count = 0;
			foreach ( $all_dir_plugins as $plugin ) {
				if ( $plugin['active_status'] == 'active' ) {
					$active_plugins_count ++;
				} else {
					$inactive_plugins_count ++;
				}
			}
			?>
            <span>Filter by:</span>
            <ul class="gapi_filter">
                <li class="init"><a href="#"><?php echo __( 'Filter By', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="all"><a
                            href="#"><?php echo __( 'All (' . count( $all_dir_plugins ) . ')', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="active"><a
                            href="#"><?php echo __( 'Active (' . $active_plugins_count . ')', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="inactive"><a
                            href="#"><?php echo __( 'Inactive (' . $inactive_plugins_count . ')', 'ns_admin' ); ?></a>
                </li>
                <li class="select" data-plugin-status="update"><a
                            href="#"><?php echo __( 'Update Available (' . count( $all_updates_dirs ) . ')', 'ns_admin' ); ?></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="tabs">
        <nav>
			<?php foreach ( $all_dir_plugins as $dir_plugin ) :
				
				if ( ! empty( $dir_plugin['name'] ) ) {
					$update_available       = '';
					$update_available_class = '';
					if ( array_search( $dir_plugin['text_domain'], $updated_need_list ) !== false ) {
						$update_available       = '<span class="update"></span>';
						$update_available_class = 'update';
					}
					$plugin_version = get_plugin_data( $dir_plugin['plugin_filename'], false, false )['Version'];
					echo '<a class="all ' . $dir_plugin['active_status'] . ' ' . $update_available_class . ' ">' . $dir_plugin['name'] . $update_available . '<span class="plugin_version">v: ' . $plugin_version . '</span></a>';
				}
			endforeach; ?>
        </nav>
		<?php foreach ( $all_dir_plugins as $dir_plugin ) : ?>
            <div class="content">
                <label for="plugin_note">Admin Notes: </label>
				<?php if ( ! empty( get_site_option( $dir_plugin['text_domain'] . '_plugin_note' ) ) ) : ?>
                    <p class="added_note"><?php echo get_site_option( $dir_plugin['text_domain'] . '_plugin_note' ); ?></p>
                    <textarea style="display: none" name="<?php echo $dir_plugin['text_domain'] . '_plugin_note'; ?>"
                              class="plugin_note"
                              id="plugin_note"><?php echo trim( get_site_option( $dir_plugin['text_domain'] . '_plugin_note' ) ); ?></textarea>
				<?php else : ?>
                    <textarea name="<?php echo $dir_plugin['text_domain'] . '_plugin_note'; ?>"
                              id="plugin_note"></textarea>
				<?php endif; ?>
                <p><span>Description: </span><?php echo $dir_plugin['plugin_description']; ?></p>
				<?php if ( ! empty( get_site_option( $dir_plugin['text_domain'] . '_plugin_note' ) ) ) : ?>
                    <button class="add_plugin_note ns_green_button edit">Edit</button>
				<?php else : ?>
                    <button class="add_plugin_note ns_green_button add">Save Note</button>
				<?php endif; ?>
				<?php
				if ( $pluginupdates->response ) {
					foreach ( $pluginupdates->response as $pluginupdate => $values ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginupdate );
						$plugin_name = $plugin_data['Name'];
						
						do_action( 'plugin_action_links_' . $dir_plugin['plugin_filename'] );
						if ( $dir_plugin['plugin_filename'] == WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $pluginupdate ) {
							wp_plugin_update_row( $pluginupdate, $plugin_data );
						}
					}
				}
				?>
                <a class="ns_wp_activation_link" href="#" data-status="<?php echo $dir_plugin['active_status']; ?>"
                   data-path="<?php echo $dir_plugin['plugin_filename']; ?>">
                    <span><?php echo $dir_plugin['active_text']; ?></span> <?php echo $dir_plugin['name']; ?>
                </a>
            </div>
		<?php endforeach; ?>
    </div>
</div>
