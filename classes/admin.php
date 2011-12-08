<?php

/**
 * Admin class, handles the options page
 */
class SC_Admin
{
	
	/**
	 * Add the link to the options page, only if required by one of the providers
	 * 
	 * @returns	void
	 */
	static function add_options_page()
	{
		if (apply_filters('social_connect_enable_options_page', false))
		{
			add_options_page('Social Connect', 'Social Connect', 'manage_options', 'social-connect-id', array('SC_Admin','render_options') );
		}
	}
	
	/**
	 * Render the options page
	 * 
	 * @returns	void							
	 */
	static function render_options()
	{
		?>
		<div class="wrap">
			<h2><?php _e('Social Connect Settings', 'social_connect'); ?></h2>
	
			<form method="post" action="options.php">
				<?php settings_fields( 'social-connect-settings-group' ); ?>
				
				<?php do_action('social_connect_options'); ?>
				
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes' ) ?>" />
				</p>

				<h2><?php _e('Rewrite Diagnostics', 'social_connect'); ?></h2>
				<p><?php _e('Click on the link below to confirm your URL rewriting and query string parameter passing are setup correctly on your server. If you see a "Test was successful" message after clicking the link then you are good to go. If you see a 404 error or some other error then you need to update rewrite rules or ask your service provider to configure your server settings such that the below URL works correctly.', 'social_connect'); ?></p>
				<p><a class="button-primary" href='<?php echo SOCIAL_CONNECT_PLUGIN_URL ?>/diagnostics.php?testing=http://www.example.com' target='_blank'><?php _e('Test server redirection settings', 'social_connect'); ?></a></p>
				<p>If you web server fails this test, please have your hosting provider whitelist your domain on <em>mod_security</em>. Learn more on the <a href="http://wordpress.org/extend/plugins/social-connect/faq/">Social Connect FAQ</a>.
			</form>
		</div>
		<?php
	}
	
}

?>