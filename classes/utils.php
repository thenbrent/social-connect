<?php

/**
 * Utils class, houses methods that couldn't find a good home elsewhere
 * or that just aren't class specific
 */
class SC_Utils {
	
	/**
	 * Get user id by a meta field entry
	 * 
	 * @param   string          $meta_key		
	 * @param   string          $meta_value
	 * 
	 * @returns int|bool							
	 */
	static function get_user_by_meta( $meta_key, $meta_value ) {
		global $wpdb;
	
		$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
		return $wpdb->get_var( $wpdb->prepare( $sql, $meta_key, $meta_value ) );
	}
	
	/**
	 * Generate authentication signature
	 * 
	 * @param   string          $data			
	 * @returns string							
	 */
	static function generate_signature( $data ) {
		return hash( 'SHA256', AUTH_KEY . $data );
	}
	
	/**
	 * Verify if signatures match
	 * 
	 * @param   string          $data			
	 * @param   string          $signature		
	 * @param   string          $redirect_to
	 * 
	 * @returns void							
	 */
	static function verify_signature( $data, $signature, $redirect_to ) {
		$redirect_to = SC_Utils::redirect_to();
		
		$generated_signature = SC_Utils::generate_signature( $data );
	
		if( $generated_signature != $signature ) {
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}
	
	/**
	 * get_contents alternative that uses curl (faster)
	 *  
	 * @param   string          $url			
	 * @returns string							
	 */
	static function curl_get_contents( $url ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
	
		$html = curl_exec( $curl );
	
		curl_close( $curl );
	
		return $html;
	}
	
	/**
	 * Get the redirect uri, returns admin uri if the redirect_to REQUEST is not defined
	 *  
	 * @returns	string							
	 */
	static function redirect_to()
	{
		if ( isset( $_REQUEST[ 'redirect_to' ] ) && $_REQUEST[ 'redirect_to' ] != '' )
		{
			$redirect_to = $_REQUEST[ 'redirect_to' ];
			
			// Redirect to https if user wants ssl
			if ( isset( $secure_cookie ) && $secure_cookie && false !== strpos( $redirect_to, 'wp-admin') )
			{
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}
		}
			else
		{
			$redirect_to = admin_url();
		}
		
		return apply_filters( 'social_connect_redirect_to', $redirect_to );
	}
	
	/**
	 * Load an authentication provider
	 *  
	 * @param   string          $provider		
	 * @param   string|bool     $method			
	 * @returns mixed
	 */
	static function load_provider($provider, $method = false)
	{
		$provider = strtolower($provider);
		
		$provider = (object) array(
			'provider'   => trim($provider),
			'plugin'    => 'sc-provider-' . $provider . '/sc-provider-' . $provider . '.php',
			'file'       => SOCIAL_CONNECT_PLUGIN_PATH . '/../sc-provider-' . $provider . '/sc-provider-' . $provider . '.php',
			'class'     => 'SC_Provider_' . ucfirst($provider)
		);
		
		// Validate if provider plugin is enabled
		$ap = (array) get_option( 'active_plugins', array() );
		if ( !in_array($provider->plugin,$ap))
		{
			return false;
		}
		
		// Validate if provider plugin still exists
		if ( !file_exists($provider->file))
		{
			return false;
		}
		require_once $provider->file;
		
		// Validate if provider plugin has a properly named class
		if ( !class_exists($provider->class))
		{
			return false;
		}
		
		// Call method if provided
		if ($method)
		{
			call_user_func(array($provider->class,$method));
		}
		
		return $provider;
	}
	
}