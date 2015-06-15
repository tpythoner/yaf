<?php
/**
 * user_index controller
 */
class Users_IndexController extends Ctrl_Base
{
	public function indexAction($name = "Stranger") 
	{
		echo $name;
		print_r($_GET);
		return FALSE;
	}

	public function updateAction($name = 'admin') 
	{
		echo $name;
		Tool_Fnc::dump(Yaf_Registry::get("config"));
		exit('what\'s your name?');
	}
}
