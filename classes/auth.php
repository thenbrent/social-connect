<?php

class SC_Auth {
	
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
	
	static function process_login( $is_ajax = false )
	{
		$redirect_to = SC_Utils::redirect_to();
	
		$social_connect_provider    = $_REQUEST[ 'social_connect_provider' ];
		$provider_identity_key      = 'social_connect_' . $social_connect_provider . '_id';
		
		if ( ! $gateway = SC_Utils::load_gateway($social_connect_provider))
		{
			return;
		}
		
		// Cookies used to display welcome message if already signed in recently using some provider
		setcookie(
			"social_connect_current_provider",  // name
			$social_connect_provider,           // value
			time()+3600,                        // expire
			SITECOOKIEPATH,                     // path
			COOKIE_DOMAIN                       // domain 
		);
		
		$data 		= call_user_func(array($gateway->class,'process_login'));
		$user_id 	= SC_Utils::get_user_by_meta( $provider_identity_key, $data->provider_identity );
		
		if ( $user_id )
		{
			$user_data  = get_userdata( $user_id );
			$user_login = $user_data->user_login;
		}
			// User not found by provider identity, check by email
			elseif ( $user_id = email_exists( $data->email ) )
		{ 
			update_user_meta( $user_id, $provider_identity_key, $data->provider_identity );
	
			$user_data  = get_userdata( $user_id );
			$user_login = $user_data->user_login;
		}
			else // Create new user and associate provider identity
		{ 
			$user_login = static::get_unique_username($data->user_login);
	
			$user_create = array(
				'user_login'    => $user_login,
				'user_email'    => $data->email,
				'first_name'     => $data->first_name,
				'last_name'     => $data->last_name,
				'user_url'      => $data->profile_url,
				'user_pass'     => wp_generate_password()
			);
	
			// Create a new user
			$user_id = wp_insert_user( $user_create );
	
			if ( $user_id && is_integer( $user_id ) )
			{
				update_user_meta( $user_id, $provider_identity_key, $data->provider_identity );
			}
				else
			{
				return;
			}
		}
	
		wp_set_auth_cookie( $user_id );
	
		do_action( 'social_connect_login', $user_login );
	
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
	
	static function get_unique_username($user_login, $c = 1)
	{
		if ( username_exists( $user_login ) )
		{
			if ($c > 5)
			{
				$append = '_'.substr(uniqid(),-3);
			}
				else
			{
				$append = $c;
			}
			
			$user_login = apply_filters( 'social_connect_username_exists', $user_login . $append );
			
			return static::get_unique_username($user_login,++$c);
		}
			else
		{
			return $user_login;
		}
	}
	
}