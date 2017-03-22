<?php
//ini_set('display_errors', 'on');
use Workerman\Worker;
define('GLOBAL_START', 1);
// 自动加载类
require_once __DIR__ . './vendor/autoload.php';

//心跳间隔
define('HEARTBEAT_TIME', 25);
$worker = new Worker('http://0.0.0.0:8686');
$worker->name = 'SteamWorker';
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
$worker->onWorkerStart = function ($worker) {

	//require_once 'steam.php';
	//global $cookie_info;
	//global $userinfo;
	//if ($cookie_info != ''){
	//    Timer::add(HEARTBEAT_TIME, 'send_cookie', array($cookie_info, $userinfo, HEARTBEAT_TIME));
	//}
};

// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function ($connection, $data) use ($worker) {
	//require_once 'steam.php';
	//global $cookie_info;
	//global $userinfo;
	//echo $userinfo . PHP_EOL;
	//if ($cookie_info != ''){
	//    Timer::add(HEARTBEAT_TIME, 'send_cookie', array($cookie_info, $userinfo, HEARTBEAT_TIME));
	//}

	$connection->send('Connect success.Please Login.');
};

// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
// $worker->onConnect = function ($connection) {
// 	$connection->onWebSocketConnect = function ($connection, $http_header) {
//         // 可以在这里判断连接来源是否合法，不合法就关掉连接
// 		// $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
// 		if ($_SERVER['HTTP_ORIGIN'] != 'http://www.domain.com') {
// 			$connection->close();
// 		}
//         // onWebSocketConnect 里面$_GET $_SERVER是可用的
// 		// var_dump($_GET, $_SERVER);
// 	};
// };

// 当有客户端连接断开时
$worker->onClose = function ($connection) use ($worker) {
	global $worker;
	if (isset($connection->uid)) {
		// 连接断开时删除映射
		unset($worker->uidConnections[$connection->uid]);
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
		return false;
	}

	if (!$userinfo) {
		return false;
	}

	$web_url = 'http://steamcommunity.com/profiles/' . $userinfo['steamid'] . '/home';
	https_post($web_url, $cookie_info, $timeout);
}

// 运行worker
Worker::runAll();
