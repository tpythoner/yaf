<?php
/**
 * shell
 */

class Cli_TipsController extends Ctrl_Cli
{
	
	public function tipsAction($name = "tony") 
	{
		print_r($name);
		echo "\n";
		//Tool_Fnc::dump(Yaf_Registry::get("config"));
		exit('what\'s your name?');
	}
}
