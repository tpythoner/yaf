<?php
/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class SampleModel extends Orm_Base
{
	public $table = 'tb_user';
	public $field = array(
		'id' => array('type' => "int", 'comment' => 'id'),
		'username' => array('type' => "varchar", 'comment' => '用户名'),
		'password' => array('type' => "varchar", 'comment' => '密码'),
		'email' => array('type' => "varchar", 'comment' => '邮箱'),
		'logincount' => array('type' => "mediumint", 'comment' => '登录次数'),
		'lastip' => array('type' => "varchar", 'comment' => '最后登录IP'),
		'lastlogin' => array('type' => "datetime", 'comment' => '最后登录书剑'),
		'authkey' => array('type' => "char", 'comment' => '登录key'),
		'active' => array('type' => "tinyint", 'comment' => '是否激活'),

	);
	public $pk = 'id';
	
	public function selectSample()
	{
		$sData = $this->field('id, username, logincount, active')->fList();
		return $sData;
	}
	
	public function insertSample($arrInfo)
	{
		return true;
	}

}
