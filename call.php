<?php

require_once( dirname(__FILE__) . '/../../../wp-load.php' );

class SC_Call {
	
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