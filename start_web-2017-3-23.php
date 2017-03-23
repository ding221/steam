<?php
//ini_set('display_errors', 'on');
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Protocols\Http;

define('GLOBAL_START', 1);
// 自动加载类
require_once '../vendor/autoload.php';
//加载通用函数库
require_once '../common/func.php';
require_once  '../conf/cfg.php';

//心跳间隔
define('HEARTBEAT_TIME', 25);
$worker = new Worker('http://0.0.0.0:8686');
$worker->name = 'SteamWorker';
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
$worker->onWorkerStart = function ($worker) {
    //$timer_id = Timer::add(60, function()use(&$timer_id, &$count)
    //{
    //    global $login;
    //    $login = User::login();
    //    // 运行10次后销毁当前定时器
    //    if(count($login) || $count++ >= 10)
    //    {
    //        Timer::del($timer_id);
    //    }
    //});
    //User::login();
    //global $cookie_info;
    //global $userinfo;
    ////global $login;
    //Timer::add(HEARTBEAT_TIME, 'send_cookie', [$cookie_info, $userinfo, HEARTBEAT_TIME]);
	Timer::add(HEARTBEAT_TIME, function()use($worker){
		println("send data!");
	});

};

// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function ($connection, $data) use ($worker) {
	// 判断当前客户端是否已经验证,既是否设置了uid
	//if (!isset($connection->uid)) {
    //
     //   //$worker->uidConnections[$connection->uid] = $connection;
    //
	//	//return $connection->send('login success, your uid is ' . $connection->uid);
	//	// return sendMessageByUid($connection->uid, '');
	//}
	Http::input(json_encode($data), $connection);
	Http::sessionStart();
	$uri = ltrim($_SERVER["REQUEST_URI"], '/');

	if ($uri != ''){
		$place = strstr($uri, '?');
		if ($place !== false){
			$uri = strstr($uri, '?', true);
		}
		$uri = explode('/', $uri);

		if (count($uri) > 1) {
			$c = 'App\\Http\\'; //加载控制器的命名空间
			$c .= ucfirst($uri[0]);
			$a = $uri[1];
			if (class_exists($c) && method_exists($c, $a)){
				$o = new $c;
				$data = $o->$a();
				$info = [//此处需要些一个状态发送函数 get_return _date();
					'code' => 200,
					'message' => $data
				];
			} else {
				$info = [
					'code' => '404',
					'error' => get_http_status_message(404),
					'message' => '找不到该地址'
				];
			}
		} else {
			$info = [
				'code' => '400',
				'error' => get_http_status_message(400),
				'message' => 'url地址错误'
			];
		}
	} else {
		$info = [
			'code' => 200,
			'success' => get_http_status_message(200),
			'message' => ''
		];
	}
	//$connection->send(json_encode($info));;
	Http::setcookie('testSession', 'woshiceshideshuju', 3600, './cookie/', '');
	Http::header("HTTP/1.1 " . $info['code'] . " " . get_http_status_message($info['code']) . "\r\n\r\n", true, $info['code']);
	$connection->send(json_encode($info));

};

//$worker->onConnect = function ($connection) use($worker){
//	$connection->send('Connect success.');
//};
// 当有客户端连接断开时
$worker->onClose = function ($connection) use ($worker) {
	global $worker;
	if (isset($connection->uid)) {
		// 连接断开时删除映射
		unset($worker->uidConnections[$connection->uid]);
		echo $connection->uid . '断开连接';
	}
};

// 针对uid推送数据
function sendMessageByUid($uid, $message) {
	global $worker;
	if (isset($worker->uidConnections[$uid])) {
		$connection = $worker->uidConnections[$uid];
		return $connection->send($message);
	}
	return false;
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
	$result = https_post($web_url, join(';', $cookie_info), $timeout);
    if ($result)
        println('send!');
}

// 运行worker
Worker::runAll();
