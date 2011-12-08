<?php
/*
Plugin Name: Social Connect
Plugin URI: http://wordpress.org/extend/plugins/social-connect/
Description: Allow your visitors to comment, login and register with their Twitter, Facebook, Google, Yahoo or WordPress.com account.
Version: 0.10
Author: Brent Shepherd, Nathan Rijksen
Author URI: http://wordpress.org/extend/plugins/social-connect/
License: GPL2
 */

 class Social_connect {
	
	static function init()
	{
		static::add_constants();
		static::add_actions();
		
		require_once SOCIAL_CONNECT_PLUGIN_PATH . '/classes/utils.php';
		require_once SOCIAL_CONNECT_PLUGIN_PATH . '/classes/ui.php';
		require_once SOCIAL_CONNECT_PLUGIN_PATH . '/classes/admin.php';
		require_once SOCIAL_CONNECT_PLUGIN_PATH . '/classes/widget.php';
		require_once SOCIAL_CONNECT_PLUGIN_PATH . '/classes/auth.php';
		
		/**
		 * Registration.php is deprecated since version 3.1 with no alternative available.
		 * registration.php functions moved to user.php, everything is now included by default
		 * This file only need to be included for versions before 3.1.
		 */		
		if ( !function_exists( 'email_exists' ) )
		{
			require_once( ABSPATH . WPINC . '/registration.php' );
		}
	}
	
	function add_constants()
	{
		require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
		
		if( !defined( 'SOCIAL_CONNECT_PLUGIN_URL' ))
		{
			define( 'SOCIAL_CONNECT_PLUGIN_URL', plugins_url() . '/' . basename( dirname( __FILE__ )));
		}
		
		if( !defined( 'SOCIAL_CONNECT_PLUGIN_PATH' ))
		{
			define( 'SOCIAL_CONNECT_PLUGIN_PATH', dirname( __FILE__ ));
		}
	}
	
	function add_actions()
	{
		register_activation_hook( __FILE__, array('Social_connect','hook_activate') );
		
		add_action( 'init', 					array('Social_connect', 'add_localization'), -1000 );
			
		add_action( 'login_head', 				array('Social_connect', 'add_stylesheets') );
		add_action( 'wp_head', 					array('Social_connect', 'add_stylesheets') );
			
		add_action( 'admin_print_styles',		array('Social_connect', 'add_admin_stylesheets') );
			
		add_action( 'login_head', 				array('Social_connect', 'add_javascripts') );
		add_action( 'wp_head', 					array('Social_connect', 'add_javascripts') );
		
		add_action( 'login_form', 				array('SC_UI', 'render_login_form') );
		add_action( 'register_form',       		array('SC_UI', 'render_login_form') );
		add_action( 'after_signup_form',   		array('SC_UI', 'render_login_form') );
		add_action( 'social_connect_form', 		array('SC_UI', 'render_login_form') );
		
		add_action( 'comment_form_top', 		array('SC_UI', 'render_comment_form') );
		
		add_action( 'wp_footer', 				array('SC_UI', 'render_login_page_uri') );
		
		add_shortcode( 'social_connect', 		array('SC_UI', 'shortcode_handler') );
		
		add_action( 'widgets_init', 			create_function( '', 'return register_widget( "SC_Widget" );' ));
		
		add_action( 'login_form_social_connect',array('SC_Auth', 'process_login') );
		
		add_action( 'init', 					array('SC_Auth', 'ajax_login') );
		
		add_action('admin_menu', 				array('SC_Admin', 'add_options_page') );
	}
	
	function add_localization()
	{
		$plugin_dir = basename( dirname( __FILE__ ) );
		load_plugin_textdomain( 'social_connect', null, "$plugin_dir/languages" );
	}
	
	function add_stylesheets(){
		if( !wp_style_is( 'social_connect', 'registered' ) )
		{
			wp_register_style( "social_connect", SOCIAL_CONNECT_PLUGIN_URL . "/media/css/style.css" );
			wp_register_style( "jquery-ui", 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/themes/smoothness/jquery-ui.css' );
		}
	
		if ( did_action( 'wp_print_styles' ) )
		{
			wp_print_styles( 'social_connect' );
			wp_print_styles( 'jquery-ui' );
		}
			else
		{
			wp_enqueue_style( "social_connect" );
			wp_enqueue_style( "jquery-ui" );
		}
	}
	
	function add_admin_stylesheets()
	{
		if( !wp_style_is( 'social_connect', 'registered' ) )
		{
			wp_register_style( "social_connect", SOCIAL_CONNECT_PLUGIN_URL . "/media/css/style.css" );
		}
	
		if ( did_action( 'wp_print_styles' ))
		{
			wp_print_styles( 'social_connect' );
		}
			else
		{
			wp_enqueue_style( "social_connect" );
		}
	}
	
	function add_javascripts()
	{
		if( !wp_script_is( 'social_connect', 'registered' ) )
		{
			wp_register_script( "social_connect", SOCIAL_CONNECT_PLUGIN_URL . "/media/js/connect.js" );
		}
		
		wp_print_scripts( "jquery" );
		wp_print_scripts( 'jquery-ui-core' );
		wp_print_scripts( 'jquery-ui-dialog' );
		wp_print_scripts( "social_connect" );
	}
	
	function hook_activate()
	{
		/** 
		 * Check technical requirements are fulfilled before activating.
		 **/
		if ( !function_exists( 'register_post_status' ) || !function_exists( 'curl_version' ) || !function_exists( 'hash' ) || version_compare( PHP_VERSION, '5.1.2', '<' ) )
		{
			
			deactivate_plugins( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
			
			if ( !function_exists( 'register_post_status' ) )
			{
				wp_die( sprintf( __( "Sorry, but you can not run Social Connect. It requires WordPress 3.0 or newer. Consider <a href='http://codex.wordpress.org/Updating_WordPress'>upgrading</a> your WordPress installation, it's worth the effort.<br/><a href=\"%s\">Return to Plugins Admin page &raquo;</a>", 'social_connect'), admin_url( 'plugins.php' ) ), 'social-connect' );
			}
				elseif ( !function_exists( 'curl_version' ) )
			{
				wp_die( sprintf( __( "Sorry, but you can not run Social Connect. It requires the <a href='http://www.php.net/manual/en/intro.curl.php'>PHP libcurl extension</a> be installed. Please contact your web host and request libcurl be <a href='http://www.php.net/manual/en/intro.curl.php'>installed</a>.<br/><a href=\"%s\">Return to Plugins Admin page &raquo;</a>", 'social_connect'), admin_url( 'plugins.php' ) ), 'social-connect' );
			}
				elseif ( !function_exists( 'hash' ) )
			{
				wp_die( sprintf( __( "Sorry, but you can not run Social Connect. It requires the <a href='http://www.php.net/manual/en/intro.hash.php'>PHP Hash Engine</a>. Please contact your web host and request Hash engine be <a href='http://www.php.net/manual/en/hash.setup.php'>installed</a>.<br/><a href=\"%s\">Return to Plugins Admin page &raquo;</a>", 'social_connect'), admin_url( 'plugins.php' ) ), 'social-connect' );
			}
				else
			{
				wp_die( sprintf( __( "Sorry, but you can not run Social Connect. It requires PHP 5.1.2 or newer. Please contact your web host and request they <a href='http://www.php.net/manual/en/migration5.php'>migrate</a> your PHP installation to run Social Connect.<br/><a href=\"%s\">Return to Plugins Admin page &raquo;</a>", 'social_connect'), admin_url( 'plugins.php' ) ), 'social-connect' );
			}
		}
		
		$file 				= preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
		$this_plugin 		= plugin_basename(trim($file));
		$active_plugins 	= get_option('active_plugins');
		$key 				= array_search($this_plugin, $active_plugins);
		
		if ($key) // if it's 0 it's the first plugin already, no need to continue
		{ 
			array_splice($active_plugins, $key, 1);
			array_unshift($active_plugins, $this_plugin);
			update_option('active_plugins', $active_plugins);
		}
		
		do_action( 'sc_activation' );
	}
	
 }
 
 Social_connect::init();