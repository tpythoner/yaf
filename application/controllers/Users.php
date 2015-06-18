<?php
/**
 * @name UsersController
 * @author tpythoner
 * @desc user controller
 */
class UsersController extends Ctrl_Base 
{

	public function indexAction($name = "Stranger") 
	{
		if ($name == 'tony') {
			Tool_Fnc::dump($_GET);
		} else {
			print_r('This is a page');
		}
		print_r($_GET);
		return FALSE;
	}

	public function updateAction($name = 'admin') 
	{
		echo $name;
		Tool_Fnc::dump(Yaf_Registry::get("config"));
		exit('what\'s your name?');
	}

	public function selectAction()
	{
		if (isset($_GET['isyes']) && $_GET['isyes'] == 'no') {
			print_R($_GET);
		} else {
			echo 'admin';
		}
		print_r('hello');

		return FALSE;
	}
}
