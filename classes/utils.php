<?php

class SC_Utils {
	
	static function locate_template($name)
	{
		$template = locate_template(array('social-connect/' . $name));
		
		if (empty($template))
		{
			$template = SOCIAL_CONNECT_PLUGIN_PATH . '/templates/' . $name . '.php';
		}
		
		return $template;
	}
	
	static function get_user_by_meta( $meta_key, $meta_value ) {
		global $wpdb;
	
		$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
		return $wpdb->get_var( $wpdb->prepare( $sql, $meta_key, $meta_value ) );
	}
	
	static function generate_signature( $data ) {
		return hash( 'SHA256', AUTH_KEY . $data );
	}
	
	static function verify_signature( $data, $signature, $redirect_to ) {
		$redirect_to = SC_Utils::redirect_to();
		
		$generated_signature = SC_Utils::generate_signature( $data );
	
		if( $generated_signature != $signature ) {
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}
	
	static function curl_get_contents( $url ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
	
		$html = curl_exec( $curl );
	
		curl_close( $curl );
	
		return $html;
	}
	
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
	
	static function load_gateway($gateway, $method = false)
	{
		$gateway = strtolower($gateway);
		
		$gateway = (object) array(
			'gateway'   => trim($gateway),
			'plugin'    => 'sc-gateway-' . $gateway . '/sc-gateway-' . $gateway . '.php',
			'file'       => SOCIAL_CONNECT_PLUGIN_PATH . '/../sc-gateway-' . $gateway . '/sc-gateway-' . $gateway . '.php',
			'class'     => 'SC_Gateway_' . ucfirst($gateway)
		);
		
		$ap = (array) get_option( 'active_plugins', array() );
		if ( !in_array($gateway->plugin,$ap))
		{
			return false;
		}
		
		if ( !file_exists($gateway->file))
		{
			return false;
		}
		
		require_once $gateway->file;
		
		if ( !class_exists($gateway->class))
		{
			return false;
		}
		
		if ($method)
		{
			call_user_func(array($gateway->class,$method));
		}
		
		return $gateway;
	}
	
}