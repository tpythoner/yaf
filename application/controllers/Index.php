<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Ctrl_Base 
{

	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/yaf/index/index/index/name/root 的时候, 你就会发现不同
     */
	public function indexAction($name = "Stranger") 
	{
		
		$get = $this->getRequest()->getQuery("get", "default value");
		$model = new SampleModel();
		Tool_Fnc::dump($model->selectSample());

		$this->assign("content", $model->selectSample());
		$this->assign("name", $name);

		//4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
		
        return TRUE;
	}

	public function updateAction($name = 'admin') 
	{
		
		echo $name;
		Tool_Fnc::dump(Yaf_Registry::get("config"));
		exit('what\'s your name?');
	}

	public function selectAction() 
	{
		print_r($_GET);
		Tool_Fnc::dump('test');
		exit;
	}

}
