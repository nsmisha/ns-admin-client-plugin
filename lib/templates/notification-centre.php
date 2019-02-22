<div class="ns_notification_center">
    <div class="notification_centre_title section_main_title">
        <h3><?php echo __( 'Notification Center', 'ns_admin' ); ?></h3>
        <div class="notifications_caterories">
            <span><?php echo __( 'Select Category: ', 'ns_admin' ); ?></span>
            <ul>
                <li><a class="all" href=""><?php echo __( 'All', 'ns_admin' ); ?></a></li>
                <li><a class="warnings" href=""><?php echo __( 'Warnings', 'ns_admin' ); ?></a></li>
                <li><a class="errors" href=""><?php echo __( 'ERRORS', 'ns_admin' ); ?></a></li>
                <li><a class="updates" href=""><?php echo __( 'UPDATES', 'ns_admin' ); ?></a></li>
            </ul>
        </div>
    </div>

    <div class="notifications_wrp">
        <div class="ns_notifications">
			<?php
			$update_core             = get_core_updates();
			$update_plugins          = get_plugin_updates();
			$update_themes           = get_theme_updates();
			$count_themes_to_update  = 0;
			$count_plugins_to_update = 0;
			foreach ( $update_themes as $stylesheet => $theme ) {
				$count_themes_to_update ++;
			}
			foreach ( (array) $update_plugins as $plugin_file => $plugin_data ) {
				$count_plugins_to_update ++;
			}
			foreach ( (array) $update_core as $update ) {
				$count_core_to_update = count( $update );
			}
			$this->show_admin_notices();
			$plugins_to_update = get_plugin_updates();
			foreach ( $update_core as $core ) :
				?>
                <div class="notice-info">
                    <p>New <strong><a href="/wp-admin/update-core.php">core updates available</a></strong> Why don’t you
                        do something about that. </p>
                </div>
			<?php
			endforeach; ?>
        </div>
    </div>
    <div class="help_centre grid">
        <div class="help_button col-4-12">
            <a href="mailto:support@neversettle.it">Request Help</a>
        </div>
        <div class="help_text col-8-12">
            <p><?php echo __( 'NOTE: Please review your maintenance plan above. Updates will occur at this
                pre-set times. An out of date plugin does not mean your site is not working properly.
                ', 'ns_admin' ); ?>
                <strong><?php echo __( 'All updates will be billed at our standard hourly rate.', 'ns_admin' ); ?></strong>
            </p>
        </div>
    </div>
</div>