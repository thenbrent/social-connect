<?php

require_once( dirname(__FILE__) . '/../../../wp-load.php' );

/**
 * Call class
 * centralizes callback url's so the individual plugins don't have to handle this themselves
 */
class SC_Call {
	
	/**
	 * Init, static class constructor
	 * @returns	void 
	 */
	function init()
	{
		if ( !isset($_GET['provider']) OR !ctype_alpha(trim($_GET['provider'])))
		{
			return;
		}
		
		SC_Utils::load_provider($_GET['provider'],'call');
		
	}
	
}

SC_Call::init();