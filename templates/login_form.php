<div class="social_connect_ui <?php if( strpos( $_SERVER['REQUEST_URI'], 'wp-signup.php' ) ) echo 'mu_signup'; ?>">
	<?php if( $display_label !== false ) : ?>
		<div style="margin-bottom: 3px;"><label><?php _e( 'Connect with', 'social_connect' ); ?>:</label></div>
	<?php endif; ?>
	<div class="social_connect_form" title="Social Connect">
		<?php do_action('social_connect_button_list'); ?>
	</div>
	<?php $social_connect_provider = isset( $_COOKIE['social_connect_current_provider']) ? $_COOKIE['social_connect_current_provider'] : ''; ?>
</div>