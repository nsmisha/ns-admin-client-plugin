<?php
/**
 * HTML LAYOUT FOR SIDEBAR MANAGEMENT BLOCK
 */
global $menu;
global $wp_roles;
$roles       = $wp_roles->get_names();
$roles_slugs = $wp_roles->roles;
?>
<div class="tabs_wrapper user_management_tabs"><!-- container start -->
    <div class="sidebar_management_title section_main_title">
        <h3> <?php echo __( 'Sidebar Management', 'ns_admin' ); ?> </h3>
        <div class="gapi_filter_wrp sidebar_management_filter">
            <span>Filter by:</span>
            <ul class="gapi_filter">
                <li class="init"><a href="#"><?php echo __( 'Filter By', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="all_menus"><a href="#"><?php echo __( 'All', 'ns_admin' ); ?></a>
                </li>
                <li class="select" data-plugin-status="menu_visible"><a
                            href="#"><?php echo __( 'Visible', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="menu_hidden"><a
                            href="#"><?php echo __( 'Hidden', 'ns_admin' ); ?></a></li>
                <li class="select" data-plugin-status="menu_removed"><a
                            href="#"><?php echo __( 'Removed', 'ns_admin' ); ?></a></li>
            </ul>
            <ul class="user_tabs gapi_filter">

                <li class="init"><a href="#"><?php echo __( 'Select Role: ', 'ns_admin' ); ?></a></li>
				<?php
				$counter = 1;
				foreach ( $roles as $role ) :?>
                    <li class="select tab-link <?php if ( $counter == 1 ) : echo 'current'; endif; ?>"
                        data-tab="tab-<?php echo $counter; ?>">
                        <a href="#">
							<?php echo $role; ?>
                        </a>
                    </li>
					<?php $counter ++; endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="sidebar_management_names">
        <span><?php echo __( 'Menu Name', 'ns_admin' ); ?></span>
        <ul class="status_checkboxes_name">
            <li><?php echo __( 'Edit', 'ns_admin' ); ?></li>
            <li><?php echo __( 'Visible', 'ns_admin' ); ?></li>
            <li><?php echo __( 'Hidden', 'ns_admin' ); ?></li>
            <li><?php echo __( 'Removed', 'ns_admin' ); ?></li>
        </ul>
        <div class="clear"></div>
    </div>
	<?php
	$counter = 1;
	foreach ( $roles_slugs as $role_slug => $value ) :?>
        <div id="tab-<?php echo $counter; ?>" data-tab-role="<?php echo $role_slug; ?>"
             class="tab-content top-level-role-wrp <?php if ( $counter == 1 ) : echo 'current'; endif; ?>">
            <ul class="<?php echo $role_slug; ?>">
				<?php foreach ( $menu as $item ) :
					
					// Get name of menu item
					$name = isset( $item[0] ) ? $item[0] : null;
					
					// Get dashboard item file
					$file = isset( $item[2] ) ? $item[2] : null;
					
					//Get menu item id
					$id = isset( $item[5] ) ? $item[5] : null;
					
					// Get URL for item
					$url    = $this->get_admin_menu_item_url( $file );
					
					if ( ! empty( $id ) ) :
						
						$menu_status = '';
						$to_replace  = array( '?', '=', "  " );
						$id = str_replace( $to_replace, array( "-", "-", "" ), $id );
						
						$users_capabilities = get_site_option( 'sidebar_management_capabilities' );
//                    print_r($users_capabilities);
						$current_role = [];
						if ( $users_capabilities ) {
							foreach ( $users_capabilities as $role ) {
								
								if ( $role['role'] == $role_slug ) {
									if ( ! empty( $role['menu_obj'] ) ) {
										foreach ( $role['menu_obj'] as $key => $value ) {
											$current_role[ $key ] = $value;
										}
									}
								}
								
							}
						}
						$checked_visible = '';
						$checked_hidden  = '';
						$checked_removed = '';
						$nothing_checked = '';
						
						if ( isset( $current_role[ $id ] ) && $current_role[ $id ] == 'visible' ) {
							$checked_visible = 'checked="checked"';
							$menu_status     = 'menu_visible';
						} elseif ( isset( $current_role[ $id ] ) && $current_role[ $id ] == 'hidden' ) {
							$checked_hidden = 'checked="checked"';
							$menu_status    = 'menu_hidden';
						} elseif ( isset( $current_role[ $id ] ) && $current_role[ $id ] == 'removed' ) {
							$checked_removed = 'checked="checked"';
							$menu_status     = 'menu_removed';
						} else {
							$nothing_checked = 'checked="checked"';
						}
						
						if ( ! empty( $name ) ):
							?>

                            <li class="ns_menu_item_element all_menus <?php echo $menu_status; ?>">
                                <span class="menu_item_name"><?php echo $name; ?></span>
                                <div class="status_checkboxes"
                                     data-current-user-role="<?php echo wp_get_current_user()->roles[0]; ?>">
                                    <a data-remodal-target="<?php echo $id; ?>" class="dashicons-admin-generic"
                                       href="#"></a>
                                    <div class="checkbox_wrp">
                                        <input type="radio" name="menu_status-<?php echo $role_slug . '-' . $id; ?>"
                                               value="visible"
                                               data-menu-id="<?php echo $id; ?>" <?php echo $nothing_checked . $checked_visible; ?>
                                               class="tog">
                                    </div>
                                    <div class="checkbox_wrp">
                                        <input type="radio" name="menu_status-<?php echo $role_slug . '-' . $id ?>"
                                               value="hidden"
                                               data-menu-id="<?php echo $id; ?>" <?php echo $checked_hidden; ?>
                                               class="tog">
                                    </div>
                                    <div class="checkbox_wrp">
                                        <input type="radio" name="menu_status-<?php echo $role_slug . '-' . $id ?>"
                                               value="removed"
                                               data-menu-id="<?php echo $id; ?>" <?php echo $checked_removed; ?>
                                               class="tog">
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="remodal menu_icon_remodal" data-remodal-id="<?php echo $id; ?>"
                                     data-remodal-options="hashTracking: false, closeOnOutsideClick: true">
                                    <div class="back_wrp">
                                        <button data-remodal-action="close"
                                                class="close_users_modal dashicons-arrow-left-alt2">Back
                                        </button>
                                    </div>
									
									<?php
									$wp_dashicons    = $this->wp_list_dashicons();
									$menu_icons_data = get_site_option( 'ns_wp_menu_icon_data' );
									?>
                                    <label for="<?php echo $id; ?>"><?php echo __( 'Sidebar Item Text (visible in sidebar - keep it as short as possible - leave blank for default)', 'ns_admin' ); ?></label>
                                    <input type="text" placeholder="Name" name="name" class="<?php echo $id; ?>"
                                           id="<?php echo $id; ?>">
                                    <div class="dashicons_modal">
                                        <p class="dashicons_text"><?php echo __( 'Select an Icon from Below to represent this menu item', 'ns_admin' ); ?></p>
										<?php
										if ( ! empty( $wp_dashicons ) ) {
											foreach ( $wp_dashicons as $icon ) {
												$selected = '';
												if ( isset( $menu_icons_data[ $id ] ) ) {
													$selected = $menu_icons_data[ $id ]['icon'] == 'dashicons-' . $icon . ' ' ? 'selected' : '';
												}
												echo '<div class="dashicons-' . $icon . ' ' . $selected . '"></div>';
											}
										}
										?>
                                    </div>
                                    <a class="menu_info_save" href="#">Save</a>
                                </div>
                            </li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
            </ul>

        </div>
        <button data-user="<?php echo $role_slug; ?>" id="tab-<?php echo $counter; ?>"
                data-tab-role="<?php echo $role_slug; ?>"
                class="menu_capability_button ns_green_button tab-content <?php if ( $counter == 1 ) : echo 'current'; endif; ?> ">
            Save
        </button>
		<?php $counter ++; endforeach; ?>
</div>