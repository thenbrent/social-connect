<?php

require_once( dirname(__FILE__) . '/../../../wp-load.php' );

class SC_Call {
	
	function init()
	{
		if ( !isset($_GET['gateway']) OR !ctype_alpha(trim($_GET['gateway'])))
		{
			return;
		}
		
		SC_Utils::load_gateway($_GET['gateway'],'call');
		
	}
	
}

SC_Call::init();