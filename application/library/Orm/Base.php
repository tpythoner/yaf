<?php
class Orm_Base{
	/**
	 * 数据库链接
	 * @var obj
	 */
	protected $db;

	/**
	 * 查询参数
	 * @var array
	 */
	public $options = array();

	/**
	 * PDO 实例化对象
	 * @var object
	 */
	static $instance = array();

    /**
     * model实例化对象
     */
    static $instance_model = array();

	/**
	 * 配置
	 * @var string
	 */
	protected $_config;

	/**
	 * 错误信息
	 */
	public $error = array();

    /**
     * 是否开启事务
     */
	private $_begin_transaction = false;

	/**
	 * 锁表语句
	 * @var string
	 */
	protected $_lock = '';

    /**
     * sql缓存设置
     */
	private $cache = array();

	/**
	 * 构造函数
	 */
	public function __construct($pPK = 0, $pConfig = 'default')
	{
		$this->_config = $pConfig;
		# 通过主键取出数据
		if ($pPK && $pPK = abs($pPK)) {
			if ($tRow = $this->fRow($pPK)) {
				foreach($tRow as $k1 => $v1) $this->$k1 = $v1;
			} else {
				foreach($this->field as $k1 => $v1) $this->$k1 = false;
			}
		}
    }

	/**
	 * 数据库连接
	 */
    public static function &instance($pConfig = 'default')
    {
		if (empty(self::$instance[$pConfig])) { 
			# 实例化PDO
			$tDB = Yaf_Registry::get("config")->db->$pConfig->toArray();
			self::$instance[$pConfig] = new PDO($tDB['dsn'], $tDB['username'], $tDB['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		}
		return self::$instance[$pConfig];
	}
    /**
     * model实例化
     */
    public static function getInstance()
    {
        $class_now = get_called_class();
        if (empty(self::$instance_model[$class_now])) {
            self::$instance_model[$class_now] = new $class_now;
        }
        return self::$instance_model[$class_now];
    }

	/**
	 * 特殊方法实现
	 * @param string $pMethod
	 * @param array $pArgs
	 * @return mixed
	 */
	public function __call($pMethod, $pArgs)
	{
		# 连贯操作的实现
		if (in_array($pMethod, array('field', 'table', 'where', 'order', 'limit', 'page', 'having', 'group', 'lock', 'distinct'), true)) {
			$this->options[$pMethod] = $pArgs[0];
			return $this;
		}
		# 统计查询的实现
		if (in_array($pMethod, array('count', 'sum', 'min', 'max', 'avg'))) {
			$field = isset($pArgs[0])? $pArgs[0]: '*';
			return $this->fOne("$pMethod($field)");
		}
		# 根据某个字段获取记录
		if ('ff' == substr($pMethod, 0, 2)) {
			return $this->where(strtolower(substr($pMethod, 2)) . "='{$pArgs[0]}'")->fRow();
		}
	}

	/**
	 * 过滤危险数据
	 * @param array $pData
	 */
	private function _filter(&$pData) 
	{
		foreach ($pData as $k1 => &$v1) {
			if (empty($this->field[$k1])) {
				unset($pData[$k1]);
				continue;
			}
			$v1 = strtr($v1, array('\\' => '', "'" => "\'"));
		}
		return $pData ? true: false;
	}

	/**
	 * 查询参数
	 * @param mixed $pOpt
	 */
	private function _options($pOpt = array())
	{
		# 合并查询条件
		$tOpt = $pOpt ? array_merge($this->options, $pOpt): $this->options;
		$this->options = array();
		# 数据表
		empty($tOpt['table']) && $tOpt['table'] = $this->table;
		empty($tOpt['field']) && $tOpt['field'] = '*';
		#  查询条件
        if (isset($tOpt['where']) && is_array($tOpt['where'])) {
            foreach ($tOpt['where'] as $k1 => $v1) {
                if (isset($this->field[$k1]) && is_scalar($v1)) {
                    # 整型格式化
                    if (false !== strpos($this->field[$k1]['type'], 'int')) {
                        $tOpt['where'][$k1] = intval($v1);
                    } elseif(false !== strpos($this->field[$k1]['type'], 'float')) {
                        $tOpt['where'][$k1] = floatval($v1);
                    }
                }
		    }
        }
		return $tOpt;
	}

	/**
	 * 执行SQL
	 */
	public function exec($pSql)
	{
		$this->db = &self::instance($this->_config);
		if ($tReturn = $this->db->exec($pSql)) {
			$this->error = array();
		} else {
			$this->error = $this->db->errorInfo();
			isset($this->error[1]) || $this->error = array();
		}
		return $tReturn;
	}

	/**
	 * 设置出错信息
	 * @param $pMsg 信息
	 * @param int $pCode 错误码
	 * @param string $pState SQL错误码
	 */
	public function setError($pMsg, $pCode = 1, $pState = 'BTC001')
	{
		$this->error = array($pState, $pCode, $pMsg);
		return false;
	}

	/**
	 * 开启本次查询缓存
	 * @param str $pKey MemKey
	 * @param int $pExpire 有效期
	 */
	public function cache($pKey = 'md5', $pExpire = 86400)
	{
		$this->cache['key'] = $pKey;
		$this->cache['expire'] = $pExpire;
		return $this;
	}

	/**
	 * 执行SQL，并返回结果
	 */
	public function query()
	{
		$tArgs = func_get_args();
		$tSql = array_shift($tArgs);
		# 锁表查询
		if ($this->_lock) {
			$tSql.= ' '.$this->_lock;
			$this->_lock = '';
		}
		# 使用缓存
		if ($this->cache) {
			$tMem = &Cache_Redis::instance('mysql');
			if ('md5' == $this->cache['key']) { 
				$this->cache['key'] = md5($tSql . ($tArgs ? join(',', $tArgs): ''));
			}
			if (false !== ($tData = $tMem->get($this->cache['key']))) {
				return json_decode($tData, true);
			}
		}
		# 查询数据库
		$this->db = &self::instance($this->_config);
		if ($tArgs) {
			$tQuery = $this->db->prepare($tSql);
			$tQuery->execute($tArgs);
		} else {
			$tQuery = $this->db->query($tSql);
		}
		if (!$tQuery) {
			$this->error = $this->db->errorInfo();
			isset($this->error[1]) || $this->error = array();
			return array();
		}
		$tData = $tQuery->fetchAll(PDO::FETCH_ASSOC);
		# 不缓存查询结果
		if (!$this->cache) {
			return $tData;
		}
		# 设置缓存
        $tMem->set($this->cache['key'], json_encode($tData));
        $tMem->expire($this->cache['key'], $this->cache['expire']);
		$this->cache = array();
		return $tData;
	}

	/**
	 * 保存记录(自动区分 增/改)
	 */
	public function save($pData)
	{
		return isset($pData[$this->pk]) ? $this->update($pData) : $this->insert($pData);
	}

	/**
	 * 添加记录
	 */
	public function insert($pData, $pReplace = false)
	{
		if ($this->_filter($pData))
		{
			$tField = '`'.join('`,`', array_keys($pData)).'`';
			$tVal = join("','", $pData);
			if($this->exec(($pReplace ? "REPLACE": "INSERT") . " INTO `$this->table`($tField) VALUES ('$tVal')")) {
				return $this->db->lastInsertId();
			} 
		}
		return false;
	}

	/**
	 * 更新记录
	 */
	public function update($pData)
	{
		# 过滤
        if (!$this->_filter($pData)) {
            return false;
        }
		# 条件
		$tOpt = array();
		if (isset($pData[$this->pk])) {
			$tOpt = array('where' => "$this->pk='{$pData[$this->pk]}'");
		}
		$tOpt = $this->_options($tOpt);
		# 更新
		if ($pData && !empty($tOpt['where'])) {
            foreach($pData as $k1 => $v1) {
                $tSet[] = "`$k1`='$v1'";
            }
			return $this->exec("UPDATE `" . $tOpt['table'] . "` SET " . join(',', $tSet) . " WHERE " . $tOpt['where']);
		}
		return false;
	}

	/**
	 * 查找一条
     * @param $pId pk_id或者sql语句
     * @return array()
	 */
	public function fRow($pId = 0)
	{
		if (false === stripos($pId, 'SELECT')) {
			$tOpt = $pId ? $this->_options(array('where' => $this->pk . '=' . abs($pId))): $this->_options();
			$tOpt['where'] = empty($tOpt['where'])? '': ' WHERE ' . $tOpt['where'];
			$tOpt['order'] = empty($tOpt['order'])? '': ' ORDER BY ' . $tOpt['order'];
			$tSql = "SELECT {$tOpt['field']} FROM `{$tOpt['table']}` {$tOpt['where']} {$tOpt['order']}  LIMIT 0,1";
		} else {
			$tSql = &$pId;
		}
		if ($tResult = $this->query($tSql)) {
			return $tResult[0];
		}
		return array();
	}

	/**
	 * 查找一字段 ( 基于 fRow )
	 *
	 * @param string $field
	 * @return string
	 */
	public function fOne($field)
	{
		$this->field($field);
		if (($tRow = $this->fRow()) && isset($tRow[$field])) {
			return $tRow[$field];
		}
		return false;
	}

	/**
	 * 查找多条
	 */
	public function fList($pOpt = array())
	{
		if (!is_array($pOpt)) {
			$pOpt = array('where' => $this->pk . (strpos($pOpt, ',') ? ' IN(' . $pOpt . ')': '=' . $pOpt));
		}
		$tOpt = $this->_options($pOpt);
		$tSql = "SELECT {$tOpt['field']} FROM  `{$tOpt['table']}`";
		$this->join && $tSql .= implode(' ', $this->join);
		empty($tOpt['where']) || $tSql .= ' WHERE ' . $tOpt['where'];
		empty($tOpt['group']) || $tSql .= ' GROUP BY ' . $tOpt['group'];
		empty($tOpt['order']) || $tSql .= ' ORDER BY ' . $tOpt['order'];
		empty($tOpt['having']) || $tSql.= ' HAVING '.$tOpt['having'];
		empty($tOpt['limit']) || $tSql .= ' LIMIT ' . $tOpt['limit'];
		return $this->query($tSql);
	}

	/**
	 * 查询并处理为哈西数组 ( 基于 fList )
	 *
	 * @param string $field
	 * @return array
	 */
	public function fHash($field)
	{
		$this->field($field);
		$tList = array();
		$tField = explode(',', $field);
		if(2 == count($tField)) foreach($this->fList() as $v1) $tList[$v1[$tField[0]]] = $v1[$tField[1]];
		else foreach($this->fList() as $v1) $tList[$v1[$tField[0]]] = $v1;
		return $tList;
	}

	/**
	 * 库 > (所有)数据表
	 * @return array
	 */
	public function getTables()
	{
		$this->db = &self::instance($this->_config);
		return $this->db->query("SHOW TABLES")->fetchAll(3);
	}

	/**
	 * 数据表 > (所有)字段
	 * @return array
	 */
	public function getFields($pTable)
	{
		$this->db = &self::instance($this->_config);
		$tQuery = $this->db->query("SHOW FULL FIELDS FROM `$pTable`");
		return $tQuery ? $tQuery->fetchAll(2) : array();
	}

	public $join = array();
	public function join($pTable, $pWhere, $pPrefix = '')
	{
		$this->join[] = " $pPrefix JOIN `$pTable` ON $pWhere ";
		return $this;
	}

	/**
	 * 事务开始
	 */
	public function begin()
	{
		$this->db || $this->db = &self::instance($this->_config);
		# 已经有事务，退出事务
		$this->back();
		if(!$this->db->beginTransaction()) {
			return false;
		}
		$this->_begin_transaction = true;
		return true;
	}

	/**
	 * 事务提交
	 */
	public function commit()
	{
		if($this->_begin_transaction) {
			$this->_begin_transaction = false;
			$this->db->commit();
		}
		return true;
	}

	/**
	 * 事务回滚
	 */
	public function back()
	{
		if($this->_begin_transaction) {
			$this->_begin_transaction = false;
			$this->db->rollback();
		}
		return false;
	}

	/**
	 * 锁表
	 */
	public function lock($pSql = 'FOR UPDATE')
	{
		$this->_lock = $pSql;
		return $this;
	}

}
