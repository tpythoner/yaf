<?php
/**
 * shell
 */

class Cli_TipsController extends Ctrl_Cli
{
	
	public function updateAction($name = "tony") 
	{
		print_r($name);
		//Tool_Fnc::dump(Yaf_Registry::get("config"));
		exit('what\'s your name?');
	}
}
