<?php
$manager_settings  = get_site_option( static::MANAGER_SETTINGS_KEY );
$connection_status = get_site_option( 'connection_to_hub_status' );
$received_data     = get_site_option( 'received_data' );
$client_message    = isset( $received_data['client_message'] ) ? stripcslashes( $received_data['client_message'] ) : '';
?>
<div class="preloader-wrapper">
    <div class="preloader">
        <img src="<?php echo plugins_url( $this->ns_plugin_slug . '/images/loader.gif' ); ?>" alt="loading">
    </div>
</div>
<div id="ns_wp_admin_top_section">
    <div class="banner_section"
    ">
	<?php echo $client_message; ?>
	<?php if ( $manager_settings['connection_url'] && $manager_settings['connection_key'] ): ?>
        <div class="disconnect_wrp">
            <span><?php echo __( 'are you sure you\'d like to disconnect?', 'ns_admin' ); ?></span>
            <button class="break_connection"><?php echo __( 'Yes disconnect from hub all data will be lost', 'ns_admin' ); ?></button>
            <button class="stay_connected"><?php echo __( 'No stay connected!', 'ns_admin' ); ?></button>
        </div>
        <div class="connect_wrapper">
            <input type="text" name="remote_site_url" placeholder="Hub Url"
                   value="<?php echo $manager_settings['connection_url']; ?>">
            <input type="text" name="remote_site_key" placeholder="Client Key"
                   value="<?php echo $manager_settings['connection_key']; ?>">
            <button class="connect">Connect with Hub</button>
			<?php if ( $connection_status == 'success' ): ?>
                <span>OR</span>
                <button class="disconnect"><?php echo __( 'Disconnect', 'ns_admin' ); ?></button>
			<?php endif; ?>
        </div>
		
		<?php if ( $connection_status == 'success' ): ?>
            <button class="connection_status"><?php echo __( 'Connected', 'ns_admin' ); ?></button>
		<?php elseif ( $connection_status == 'error' ): ?>
            <button class="connection_status"><?php echo __( 'Not Connected', 'ns_admin' ); ?></button>
		<?php else: ?>
            <button class="connection_status"><?php echo __( 'Not Connected', 'ns_admin' ); ?></button>
		<?php endif; ?>
	
	<?php else : ?>
        <div class="connect_wrapper">
            <input type="text" name="remote_site_url" placeholder="Hub Url"
                   value="<?php echo $manager_settings['connection_url']; ?>">
            <input type="text" name="remote_site_key" placeholder="Client Key"
                   value="<?php echo $manager_settings['connection_key']; ?>">
            <button class="connect"><?php echo __( 'Connect with Hub', 'ns_admin' ); ?></button>
        </div>
        <button class="connection_status"><?php echo __( 'Connect with Hub', 'ns_admin' ); ?></button>
	<?php endif; ?>
</div>
<?php
//$received_data = false;
if ( $received_data ) : ?>
	<?php
	$rep_data  = unserialize( $received_data['partner_data'] );
	$rep_image = $rep_data['partner_image_url'] ? $rep_data['partner_image_url'] : plugins_url( $this->ns_plugin_slug . '/images/stephen.png' );
	$rep_name  = $rep_data['partner_name'] ? $rep_data['partner_name'] : __( 'Stephen Karpik', 'ns_admin' );
	$rep_email = $rep_data['partner_email'] ? $rep_data['partner_email'] : __( 'support@neversettle.it', 'ns_admin' );
	$rep_phone = $rep_data['partner_phone'] ? $rep_data['partner_phone'] : '720-432-6738';
	?>
    <div class="partner_section">
        <!--        <div class="partner_photo polygon-clip-hexagon">-->
        <!--            <img src="--><?php //echo $rep_image; ?><!--" alt="">-->
        <!--        </div>-->
        <div class="hexagon" style="background-image: url(<?php echo $rep_image; ?>)">
            <div class="hexTop"></div>
            <div class="hexBottom"></div>
        </div>
        <div class="partner_data">
            <h4><span><?php echo __( 'Your Partner Rep is', 'ns_admin' ); ?></span><?php echo $rep_name; ?></h4>
            <a class="ns_mail" href="mailto:<?php echo $rep_email; ?>"><?php echo $rep_email; ?></a>
            <a class="ns_telephone" href="tel:<?php echo $rep_phone; ?>"><?php echo $rep_phone; ?></a>
        </div>
    </div>
<?php else: ?>
    <div class="partner_section">
        <div class="hexagon"
             style="background-image: url(<?php echo plugins_url( $this->ns_plugin_slug . '/images/sdk.jpg' ); ?>)">
            <div class="hexTop"></div>
            <div class="hexBottom"></div>
        </div>
        <div class="partner_data">
            <h4>
                <span><?php echo __( 'Your Partner Rep is', 'ns_admin' ); ?></span><?php echo __( 'Stephen Karpik', 'ns_admin' ); ?>
            </h4>
            <a class="ns_mail"
               href="mailto:support@neversettle.it"><?php echo sanitize_email( 'support@neversettle.it' ); ?></a>
            <a class="ns_telephone" href="tel:720-365-8393"><?php echo '720-365-8393'; ?></a>
        </div>
    </div>
<?php endif; ?>
</div>