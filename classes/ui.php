<?php

class SC_UI {
	
	static function render_login_form( $args = NULL )
	{
	
		if ( $args == NULL )
		{
			$display_label = true;
		}
			elseif ( is_array( $args ) )
		{
			extract( $args );
		}
	
		?>
		<div class="social_connect_ui <?php if( strpos( $_SERVER['REQUEST_URI'], 'wp-signup.php' ) ) echo 'mu_signup'; ?>">
			<?php if( $display_label !== false ) : ?>
				<div style="margin-bottom: 3px;"><label><?php _e( 'Connect with', 'social_connect' ); ?>:</label></div>
			<?php endif; ?>
			<div class="social_connect_form" title="Social Connect">
				<?php do_action('social_connect_button_list'); ?>
			</div>
		</div>
		<?php
	}
	
	static function add_comment_meta( $comment_id )
	{
		$social_connect_comment_via_provider = isset( $_POST['social_connect_comment_via_provider']) ? $_POST['social_connect_comment_via_provider'] : '';
		
		if ( $social_connect_comment_via_provider != '' )
		{
			update_comment_meta( $comment_id, 'social_connect_comment_via_provider', $social_connect_comment_via_provider );
		}
	}
	
	static function render_comment_meta( $link )
	{
		global $comment;
		
		$images_url = SOCIAL_CONNECT_PLUGIN_URL . '/media/img/';
		$social_connect_comment_via_provider = get_comment_meta( $comment->comment_ID, 'social_connect_comment_via_provider', true );
		
		if ( $social_connect_comment_via_provider && current_user_can( 'manage_options' ))
		{
			return $link . '&nbsp;<img class="social_connect_comment_via_provider" alt="'.$social_connect_comment_via_provider.'" src="' . $images_url . $social_connect_comment_via_provider . '_16.png"  />';
		}
			else
		{
			return $link;
		}
	}
	
	static function render_comment_form()
	{
		if ( comments_open() && !is_user_logged_in())
		{
			static::render_login_form();
		}
	}
	
	static function render_login_page_uri()
	{
		echo '<input type="hidden" id="social_connect_login_form_uri" value="' . site_url( 'wp-login.php', 'login_post' ) . '" />';
	}
	
	static function shortcode_handler( $args )
	{
		if( !is_user_logged_in())
		{
			static::render_login_form();
		}
	}
	
}

