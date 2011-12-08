<?php

/**
 * Authentication class, handles login and registration
 */
class SC_Auth {
	
	/**
	 * Redirect ajax logins to process_login with a flag to indicate ajax login
	 * 
	 * @returns	void							
	 */
	static function ajax_login()
	{
		if (
			isset( $_POST[ 'login_submit' ] )   AND
			$_POST[ 'login_submit' ] == 'ajax'  AND
			isset( $_POST[ 'action' ] )         AND
			$_POST[ 'action' ] == 'social_connect'
		)
		{
			static::process_login( true );
		}
	}
	
	/**
	 * Process user login
	 * 
	 * @param	bool			$is_ajax
	 * 
	 * @returns	void							
	 */
	static function process_login( $is_ajax = false )
	{
		$redirect_to = SC_Utils::redirect_to();
	
		$social_connect_provider    = $_REQUEST[ 'social_connect_provider' ];
		$provider_identity_key      = 'social_connect_' . $social_connect_provider . '_id';
		
		if ( ! $provider = SC_Utils::load_provider($social_connect_provider))
		{
			return;
		}
		
		// Process login with the selected provider
		$data 		= call_user_func(array($provider->class,'process_login'));
		
		// Check if the authenticated user already has an account
		$user_id 	= SC_Utils::get_user_by_meta( $provider_identity_key, $data->provider_identity );
		
		if ( $user_id ) // user already has an account
		{
			$user_data  = get_userdata( $user_id );
			$user_login = $user_data->user_login;
		}
			// User already has an account but hasn't logged in with this provider before
			elseif ( $user_id = email_exists( $data->email ) )
		{
			update_user_meta( $user_id, $provider_identity_key, $data->provider_identity );
	
			$user_data  = get_userdata( $user_id );
			$user_login = $user_data->user_login;
		}
			// Create new user and associate provider identity
			else 
		{ 
			$user_login = static::get_unique_username($data->user_login);
	
			$user_create = array(
				'user_login'    => $user_login,
				'user_email'    => $data->email,
				'first_name'    => $data->first_name,
				'last_name'     => $data->last_name,
				'user_url'      => $data->profile_url,
				'user_pass'     => wp_generate_password()
			);
	
			// Add user to DB
			$user_id = wp_insert_user( $user_create );
	
			// Validate that DB insert worked
			if ( $user_id && is_integer( $user_id ) )
			{
				update_user_meta( $user_id, $provider_identity_key, $data->provider_identity );
			}
				else
			{
				return;
			}
		}
	
		// Authenticate the user 
		wp_set_auth_cookie( $user_id );
	
		// Tell everyone about our accomplishment, woo!
		do_action( 'social_connect_login', $user_login );
	
		// Notify the user depending on the request method
		if ( $is_ajax )
		{
			echo '{"redirect":"' . $redirect_to . '"}';
		}
			else
		{
			wp_safe_redirect( $redirect_to );
		}
		
		exit();
	}
	
	/**
	 * Get an available username
	 * 
	 * @param   string      $user_login
	 * @param   int         $c
	 * @returns string
	 */
	static function get_unique_username($user_login, $c = 1)
	{
		if ( username_exists( $user_login ) )
		{
			if ($c > 5)
			{
				// If we've already iterated 5 times, apply some more
				// unique alterations so we don't overload the DB
				$append = '_'.substr(uniqid(),-3);
			}
				else
			{
				$append = $c;
			}
			
			// Call filter in case a plugin wants to have a go at it
			$user_login = apply_filters( 'social_connect_username_exists', $user_login . $append );
			
			// Call this function again to ensure the generate alteration is unique
			return static::get_unique_username($user_login,++$c);
		}
			else
		{
			return $user_login;
		}
	}
	
}