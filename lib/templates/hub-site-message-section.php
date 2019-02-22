<?php
$received_data             = get_site_option( 'received_data' );
$main_hub_message          = get_site_option( 'main_message_from_hub_site' );
$current_user              = wp_get_current_user();
$client_services_active    = isset( $received_data['active_services'] ) ? unserialize( $received_data['active_services'] ) : null;
$client_services_available = isset( $received_data['active_services'] ) ? unserialize( $received_data['available_services'] ) : null;
?>
<div class="hub_site_message_block">
    <div class="client_message_header">
        <h3 class="client_welcome_title"><?php echo __( 'Welcome Back', 'ns_plugin' ) . ' ' . $current_user->display_name; ?> </h3>
    </div>
    <div class="services">
        <div class="active_services">
            <span>Active Services:</span>
			<?php
			if ( ! empty ( $client_services_active ) ):
				foreach ( $client_services_active as $active_service ) :?>
                    <a href="#">
						<?php echo $active_service; ?>
                    </a>
				<?php endforeach;
			endif;
			?>
        </div>
        <div class="available_services">
            <span>Available Services:</span>
			<?php
			if ( ! empty ( $client_services_available ) ):
				foreach ( $client_services_available as $available_service ) :?>
                    <a href="#">
						<?php echo $available_service; ?>
                    </a>
				<?php endforeach;
			endif;
			?>
        </div>
    </div>
    <div class="client_message">
        <p><?php echo $main_hub_message; ?></p>
    </div>
    <div class="contact_buttons">
        <a href="mailto:contact@neversettle.it">
            Upgrade or Change Esisting Services
        </a>
        <a href="mailto:contact@neversettle.it">
            Have a NEW Project?
        </a>
    </div>
</div>
