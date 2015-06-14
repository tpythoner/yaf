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
		echo $name;
		print_r($_GET);
		//1. fetch query
		/*$get = $this->getRequest()->getQuery("get", "default value");

		//2. fetch model
		$model = new SampleModel();

		//3. assign
		$this->getView()->assign("content", $model->selectSample());
		$this->getView()->assign("name", $name);

		//4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
		 */
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
		if(isset($_GET['isyes']) && $_GET['isyes'] == 'no') {
			print_R($_GET);
		} else {
			echo 'admin';
		}
		print_r('hello');

		return FALSE;
	}
}
