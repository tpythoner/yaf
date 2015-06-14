<?php
class Tool_Fnc{
	/**
	 * 获取以父ID为KEY的分类
	 *
	 * @param int $pPid 父ID
	 * @param int $pMeId 输出一个类别
	 */
	static function catdata($pPid = false, $pMeId = 0){
		static $datas = array();
		if(!$datas) foreach(Cache_Redis::hget('category') as $v1){
			$v1 = json_decode($v1, true);
			$datas[$v1['pid']][$v1['cid']] = $v1;
		}
		if(false === $pPid) return $datas;
		return $pMeId? $datas[$pPid][$pMeId]: $datas[$pPid];
	}

	/**
	 * 显示树状分类
	 * @param string $pBoxId 容器ID
	 * @param int $pPid 父ID (0:全部)
	 */
	static function cattree($pBoxId, $pPid = 0){
		$tDatas = self::catdata(false, 0);
		echo '<select id="yaf_', $pBoxId, '" name="', $pBoxId, '">';
		if(false !== strpos(strtolower($_SERVER['REQUEST_URI']), 'manage')){
			echo '<option value="0">顶级</option>';
		}
		self::cattreeIterate($tDatas, $pPid, 0);
		echo '</select>';
	}

	/**
	 * cattree 迭代函数
	 *
	 * @param array $datas 分类数组
	 * @param int $i 层级
	 * @param int $count 占位符个数
	 */
	static function cattreeIterate(&$datas, $i, $count){
		if(isset($datas[$i])) foreach($datas[$i] as $v1){
			echo "<option value='{$v1['cid']}'", $i == 0? " class='option'": "", ">", str_repeat('　　', $count), $v1['name'], "</option>";
			self::cattreeIterate($datas, $v1['cid'], $count + 1);
		}
	}

	/**
	 * 真实IP
	 * @return string 用户IP
	 */
	static function realip(){
		foreach(array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $v1){
			if(isset($_SERVER[$v1])){
				$tIP = ($tPos = strpos($_SERVER[$v1], ','))? substr($_SERVER[$v1], 0, $tPos): $_SERVER[$v1];
				break;
			}
			if($tIP = getenv($v1)){
				$tIP = ($tPos = strpos($tIP, ','))? substr($tIP, 0, $tPos): $tIP;
				break;
			}
		}
		return $tIP;
	}

	/**
	 * 发送邮件
	 * @param $pAddress 地址 array or string
	 * @param $pSubject 标题
	 * @param $pBody 内容
	 */
	static function mailto($pAddress, $pSubject, $pBody, $pCcAddress = NULL){
		static $mail;
		if(!$mail){
			require preg_replace( '/Tool/' ,'' , dirname(__FILE__)) . 'Source/PHPMailer/PHPmailer.php';
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'utf-8';
			$mail->SMTPAuth = true;
			$mail->Port = 25;
			$mail->Host = "smtp.exmail.qq.com";
			$mail->From = "support1@yuanbao.com";
			$mail->Username = "support1@yuanbao.com";
			$mail->Password = "0fa6187f7D1";
			$mail->FromName = "元宝团队";
			$mail->IsHTML(true);
		}
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
        if(is_array($pAddress)){
            foreach($pAddress as $v){
		        $mail->AddAddress($v);
            }
            unset($v);
        } else {
		    $mail->AddAddress($pAddress);
        }
		$pCcAddress && $mail->AddBCC($pCcAddress);
		$mail->Subject = $pSubject;
		$mail->MsgHTML(preg_replace('/\\\\/', '', $pBody));
        if($mail->Send()){
            return true;
        }
        return false;
	}

	/**
	 * 提示信息
	 * @param string $pMsg
	 * @param bool $pUrl
	 */
	/**
	 * 提示信息
	 * @param string $pMsg 信息
	 * @param bool $pUrl 跳转到
	 */
	static function showMsg($pMsg, $pUrl = false){
		is_array($pMsg) && $pMsg = join('\n', $pMsg);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if('.' == $pUrl) $pUrl = $_SERVER['REDIRECT_URL'];
		echo '<script type="text/javascript">';
		if($pMsg) echo "alert('$pMsg');";
		if($pUrl) echo "self.location='{$pUrl}'";
		elseif(empty($_SERVER['HTTP_REFERER'])) echo 'window.history.back(-1);';
		else echo "self.location='{$_SERVER['HTTP_REFERER']}';";
		exit('</script>');
	}

	/**
	 * 提示框
	 */
	static function showMsgWindow($msg,$pUrl)
	{
        is_array($pMsg) && $pMsg = join('\n', $pMsg);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if('.' == $pUrl) $pUrl = $_SERVER['REDIRECT_URL'];
		echo '<script type="text/javascript">';
		if($pMsg) echo "showErrMsg('$msg!','$pUrl','返　回')";
		exit('</script>');
	}

	/**
	 * AJAX返回
	 *
	 * @param string $pMsg 提示信息
	 * @param int $pStatus 返回状态
	 * @param mixed $pData 要返回的数据
	 * @param string $pStatus ajax返回类型
	 */
	static function ajaxMsg($pMsg = '', $pStatus = 0, $pData = '', $pType = 'json'){
		# 信息
		$tResult = array('status' => $pStatus, 'msg' => $pMsg, 'data' => $pData);
		# 格式
		'json' == $pType && exit(json_encode($tResult));
		'xml' == $pType && exit(xml_encode($tResult));
		'eval' == $pType && exit($pData);
	}

    /**
     * 接口请求
     * 新代码不再使用
     *
     * @param string $pUrl 提交的地址
     * @param string $pData 发送的数据
     *
     * @return object
     */
	static function sendHttpPostData($url, $data_string,$https=false)
	{
	    $ch = curl_init();
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	
	    $return_content = curl_exec($ch);
	    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    return json_decode($return_content);
	}
    /**
     * 以后curl使用这个命名的接口
     */
	static function httpRequest($url, $data='', $post=true)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
	    curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if($post){
	        curl_setopt($ch, CURLOPT_POST, 1);
        }
        if($data){
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
	
	    $result = curl_exec($ch);
	    return json_decode($result, true);
	}


    /**
     * 邮件模版赋值
     *
     */
    static function emailTemplate($pData , $pTemplatename){
        $pDir = preg_replace('/application\/library\/Tool/' , '' , dirname(__FILE__));
        $pTemplatedir = $pDir . 'views/cn/email_template/' . $pTemplatename . '.phtml';
        if(!is_file($pTemplatedir)){
            return false;
        }
        $pHtml = file_get_contents($pTemplatedir);

        $pKeys = array_keys($pData); 
        if(!count($pKeys)){ return false;}

        foreach($pKeys as $pKey){
           $pHtml = preg_replace('/{'.$pKey.'}/' , $pData[$pKey] , $pHtml);  
        }
        return $pHtml;
    }
    /**
     * 时间戳转换为剩余时间
     * 众筹项目使用
     */
    static function unixtimeFormat($time, $return='str'){
        if($time < 0){
            return '0时0分';
        }
        $day = intval($time / 86400);
        $hour = intval(($time - $day * 86400) / 3600);
        if($return == 'str'){
            if($day == 0){
                $minute = intval(($time - $hour * 3600)/60);
                return "{$hour}小时{$minute}分";
            }
            return "{$day}天{$hour}小时";
        } elseif ($return == 'array'){
            return array($day, $hour);
        }
        return false;
    }
    /**
     * 时间差转换为 月 天
     * @param $time 时间差
     */
    static function periodFormat($time)
    {
        $days = $time / 86400;
        if($days < 30){
            return ceil($days).'天';
        }
        return ceil($days/30).'月';
    }

	 /**
     * 时间差转换为 月 天 时 分 秒
     * @param $time 时间差
     */
    static function periodFormat_($time)
    {
        $days = $time / 86400;
		$day = floor($time / (24*3600));
        $sec = $time % (24*3600);
        $hours = floor($sec / 3600);
        $remainSeconds = $time % 3600;
        $minutes = floor($remainSeconds / 60);
		if($days < 30){
            return $day.'天'.$hours.'时'.$minutes.'分';
        }
    }
    /**
     * 获取下个月前一天
     * @param $day 时间戳 Ymd
     */
    static function getNextMonth($time)
    {
        $year = date('Y', $time);
        $month = date('m', $time);
        $day = date('d', $time);
        return mktime(0, 0, 0, $month+1, $day-1, $year);
    }
    static function multi_array_sort($multi_array,$sort_key,$sort=SORT_DESC){
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        @array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }

    /**
    * 浏览器友好的变量输出
    * @param mixed $var 变量
    * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
    * @param string $label 标签 默认为空
    * @param boolean $strict 是否严谨 默认为true
    * @return void|string
    */
    static function dump($var, $echo=true, $label=null, $strict=true) {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
    
    /**
    * URL重定向
    * @param string $url 重定向的URL地址
    * @param integer $time 重定向的等待时间（秒）
    * @param string $msg 重定向前的提示信息
    * @return void
    */
    static function redirect($url, $time=0, $msg='') {
        $url = str_replace(array("\n", "\r"), '', $url);
        if (!empty($msg)){
            $msg = $msg;
        }else {
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        }
        if (!headers_sent()) {
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }

}
