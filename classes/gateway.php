<?php

abstract class SC_Gateway
{
	
	protected static $calls = array();
	
	static function call()
	{
		if ( !isset($_GET['call']) OR !in_array($_GET['call'],static::$calls))
		{
			return;
		}
		
		call_user_func(array(get_called_class(), $_GET['call']));
	}
	
}