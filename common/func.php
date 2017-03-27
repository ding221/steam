<?php

/**
 * curl访问（get方式）
 *
 * @param string    $url
 * @param int       $timeout
 * @method get
 * @return mixed
 */
function curl_get($url, $timeout = 60, $ssl = false) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl); //CURL请求https
	//携带cookie访问
	global $cookie_info;
	if ($cookie_info != '') {
		curl_setopt($curl, CURLOPT_COOKIE, $cookie_info);
		//$cookie_file =  dirname(__FILE__) . '/cookie.txt';
		//curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
	}

	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	$result = curl_exec($curl);
	if (curl_errno($curl)) {
		return 'Errno ' . curl_error($curl);
	}
	curl_close($curl);
	return $result;
}

/**
 * curl访问（post 方式）
 *
 * @params  url     url   请求网址
 * @params  data    mixed 请求参数
 * @params  time    int   过期时间
 * return  mixed
 */
function https_post($url = '', $data = [], $time = 60, $ssl = false) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if ($ssl) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
	}

	//携带cookie访问
	global $cookie_info;
	if ($cookie_info != '') {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie_info);
		//$cookie_file =  dirname(__FILE__) . '/cookie.txt';
		//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用文件里的cookies
	}

	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, $time);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		return 'Errno ' . curl_error($ch);
	}
	curl_close($ch);
	return $result;
}

function https_post1($url = '', $data = [], $time = 60, $ssl = false, $header = null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if ($ssl) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
	}

	//携带cookie访问
	global $cookie_info;
	if ($cookie_info != '') {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie_info);
		//$cookie_file =  dirname(__FILE__) . '/cookie.txt';
		//curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用文件里的cookies
	}

	if ($header && is_array($header)){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, $time);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		return 'Errno ' . curl_error($ch);
	}
	curl_close($ch);
	return $result;
}

function get_cookie($website_url, $filepath = null) {
	$cookie_file = $filepath ? $filepath : dirname(__FILE__) . '/cookie.txt';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $website_url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file); //存储cookies
	$results = curl_exec($ch);
	curl_close($ch);
	//preg_match_all('|Set-Cookie: (.*);|U', $results, $arr);
	//if ($arr[1] == '') {
	preg_match_all('|Cookie: (.*);|U', $results, $arr);
	//}
	//return $arr[1];
	return $arr[1];
}

function send_cookie($cookie_info, $userinfo, $timeout) {
	if (!$cookie_info) {
		println('cookie false');
		return false;
	}
	if (count($userinfo) < 1) {
		println('user info false');
		return false;
	}
	$web_url = 'http://steamcommunity.com/profiles/' . $userinfo['steamid'] . '/home';
	//$result = https_post($web_url, explode(';', $cookie_info), $timeout);
	$result = curl_get($web_url, $timeout);
	if ($result) {
		println('send!');
	}

}

function get_login_info() {
	if (!file_exists(COOKIE)) {
		return false;
	}
	$time = filemtime(COOKIE);
	if (($time + 3600 * 6) < time()) {
		return false;
	}
	$data = file_get_contents(COOKIE);
	$cookie = json_decode($data, 1);
	if (isset($cookie['steamid'])) {
		return $cookie;
	} else {
		return false;
	}
}

function println($str) {
	print("$str\n");
}

function redirect($url) {
	header("Location: $url");
}

//返回指定位置的字符的 Unicode 编码
function charCodeAt($str, $index) {
	$char = mb_substr($str, $index, 1, 'UTF-8');
	if (mb_check_encoding($char, 'UTF-8')) {
		$ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
		return hexdec(bin2hex($ret));
	} else {
		return null;
	}
}

/**
 * 将ANCSII码转换为字符串
 *
 * @param $codes
 * @return string
 */
function fromCharCode($codes) {
	if (is_scalar($codes)) {
		$codes = func_get_args();
	}

	$str = '';
	foreach ($codes as $code) {
		$str .= chr($code);
	}

	return $str;
}
//fromCharCode(78,79,60);

function get_http_status_message($code = 200) {
	$http_codes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity', //发送了非法的资源
		423 => 'Locked',
		428 => 'Precondition Required', //缺少了必要的头信息,类似 -> Header User-Agent is required
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);
	//return $status = "HTTP/1.1 " . $code . ' '. $codes[$code]. "\r\n";
	return isset($http_codes[$code]) ? $http_codes[$code] : 'Unknown Error';
}

//正常请求返回的信息
function get_return_date($http_code = 200, $msg = '') {
	$return = [
		'code' => $http_code,
		//'error' => get_http_status_message($http_code),
		'msg' => $msg ? $msg : 'null',
	];
	return $return;
}

//请求出错返回的信息
function get_error_return($http_code = 422, $resource = null, $field = null, $code = null) {
	$return = [
		'code' => $http_code,
		'msg' => get_http_status_message($http_code),
		"errors" => [
			"resource" => $resource, //问题描述
			"field" => $field, //字段

			/**
			 * code表示错误类型
			 * invalid  某个字段的值非法，接口文档中会提供相应的信息
			 * required 缺失某个必须的字段
			 * not_exist 说明某个字段的值代表的资源不存在
			 * already_exist 发送的资源中的某个字段的值和服务器中已有的某个资源冲突，常见于某些值全局唯一的字段，比如 @ 用的用户名
			 */
			"code" => $code,
		],
	];
	return $return;
}

//tuki 输入内容过滤函数
function tuki_filter_input($type, $name = null, $default = null, $options = array(), $flags = null) {
	$options['default'] = $default;
	$opt = array('options' => $options);

	if (null == $name) {
		return filter_input_array($type);
	}

	$filter = FILTER_DEFAULT;
	switch (gettype($default)) {
	case 'NULL':
		break;
	case 'string':
		$filter = FILTER_SANITIZE_STRING;
		break;
	case 'boolean':
		$filter = FILTER_VALIDATE_BOOLEAN;
		break;
	case 'integer':
		$filter = FILTER_VALIDATE_INT;
		break;
	case 'double':
		$filter = FILTER_VALIDATE_FLOAT;
		break;
	case 'array':
		$flags = FILTER_FORCE_ARRAY;
		break;
	}
	$opt['flags'] = $flags;

	return filter_input($type, $name, $filter, $opt);
}

function _get($name, $default = null, $options = array(), $flags = FILTER_FLAG_NONE) {
	//return tuki_filter_input(INPUT_GET, $name, $default, $options, $flags);
	return $value = isset($_GET[$name]) ? $_GET[$name] : $default;
}

function _post($name = null, $default = null, $options = array(), $flags = null) {
	//return tuki_filter_input(INPUT_POST, $name, $default, $options, $flags);
	if ($name == '')
		return $_POST;
	return $value = isset($_POST[$name]) ? $_POST[$name] : $default;
}

function _is_get() {
	return isset($_SERVER['REQUEST_METHOD']) && 'get' == strtolower($_SERVER['REQUEST_METHOD']);
}

function _is_post() {
	return isset($_SERVER['REQUEST_METHOD']) && 'post' == strtolower($_SERVER['REQUEST_METHOD']);
}

function _is_ajax() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'];
}

function _is_https() {
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
}