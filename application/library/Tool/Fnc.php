<?php
class Tool_Fnc
{
    /**
    * 浏览器友好的变量输出
    * @param mixed $var 变量
    * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
    * @param string $label 标签 默认为空
    * @param boolean $strict 是否严谨 默认为true
    * @return void|string
    */
	static function dump($var, $echo=true, $label=null, $strict=true) 
	{
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
        } else {
            return $output;
		}
    }
    
    /**
    * URL重定向
    * @param string $url 重定向的URL地址
    * @param integer $time 重定向的等待时间（秒）
    * @param string $msg 重定向前的提示信息
    * @return void
    */
	static function redirect($url, $time=0, $msg='') 
	{
        $url = str_replace(array("\n", "\r"), '', $url);
		if (!empty($msg)) {
            $msg = $msg;
        } else {
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
            if ($time != 0) {
                $str .= $msg;
			}
            exit($str);
        }
    }

	/**
	 * 获取客户端IP
	 * @return [string] [description]
	 */
	static function realIp()
	{
		$arr = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');  
		foreach ($arr as $v) {
			if (isset($_SERVER[$v])) {
				$tIP = ($tPos = strpos($_SERVER[$v], ',')) ? substr($_SERVER[$v], 0, $tPos) : $_SERVER[$v];
				break;
			}
			if ($tIP = getenv($v)) {
				$tIP = ($tPos = strpos($tIP, ',')) ? substr($tIP, 0, $tPos) : $tIP;
				break;
			}
		}
		return $tIP;
	}

	/**
	 * 获取url
	 * @return [type] [description]
	 */
	static function getUrl()
	{
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
	  	$pageURL .= "://";
	  	if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	  	} else {
			$pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	  	}
	  	return $pageURL;
	}

	/**
 	* 获取当前站点的访问路径根目录
 	* @return [type] [description]
 	*/
	static function getSiteUrl()
	{
		$uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
		return 'http://' . $_SERVER['HTTP_HOST'] . substr($uri, 0, strrpos($uri, '/') + 1);
	}

	/**
	 * 字符串截取，支持中文和其他编码
	 * @param  [string]  $str     [字符串]
	 * @param  integer $start   [起始位置]
	 * @param  integer $length  [截取长度]
	 * @param  string  $charset [字符串编码]
	 * @param  boolean $suffix  [是否有省略号]
	 * @return [type]           [description]
	 */
	function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true) 
	{
		if(function_exists("mb_substr")) {
			return mb_substr($str, $start, $length, $charset);
		} elseif (function_exists('iconv_substr')) {
			return iconv_substr($str,$start,$length,$charset);
		}
		$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
		if ($suffix) {
			return $slice."…";
		}
		return $slice;
	}


}
