<?php
/**
* 基础类
*/
abstract class Ctrl_Base extends Yaf_Controller_Abstract{

	protected $url = '/';
	
	/**
    * 构造函数
    */
    public function init(){
        $this->assign('url', $this->url);
    }

	/**
	 * 注册变量到模板
	 */
	protected function assign($pKey, $pVal = ''){
		if(is_array($pKey)){
			$this->_view->assign($pKey);
			return $pKey;
		}
		$this->_view->assign($pKey, $pVal);
		return $pVal;
	}

}
