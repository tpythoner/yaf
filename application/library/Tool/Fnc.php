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

}
