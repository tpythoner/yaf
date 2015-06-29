<?php
/**
* 基础类
*/
abstract class Ctrl_Base extends Yaf_Controller_Abstract
{

	protected $url = '/';
	
	public function init()
	{
		$this->assign('url', $this->url);
	}
	
	protected function assign($pKey, $pVal = '')
	{
		if (is_array($pKey)) {
			$this->_view->assign($pKey);
			return $pKey;
		}
		$this->_view->assign($pKey, $pVal);
		return $pVal;
	}

}
