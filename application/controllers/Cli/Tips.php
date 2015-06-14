<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
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
