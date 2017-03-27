<?php
//ini_set('display_errors', 'on');
use App\Http\User;
use Workerman\Lib\Timer;
use Workerman\Worker;
use Workerman\Protocols\Http;

date_default_timezone_set('Asia/Singapore');

define('GLOBAL_START', 1);
define('TRADE_COOKIE', dirname(__FILE__) . '/cookie/');
// 自动加载类
require_once './vendor/autoload.php';
//加载通用函数库
require_once './common/func.php';
require_once './conf/cfg.php';

Http::header("Access-Control-Allow-Origin:*");

//心跳间隔
define('HEARTBEAT_TIME', 25);
$worker = new Worker('http://0.0.0.0:8686');
$worker->name = 'SteamWorker';
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
$worker->onWorkerStart = function ($worker) {
	//global $db;
	//$db = new Workerman\MySQL\Connection('host', 'port', 'user', 'password', 'db_name');

	User::login();
	global $cookie_info;
	global $userinfo;
	//global $login;
	if ($cookie_info && $userinfo) {
		Timer::add(HEARTBEAT_TIME, 'send_cookie', [$cookie_info, $userinfo, HEARTBEAT_TIME]);
	}

	//验证客户端是否离线
	Timer::add(HEARTBEAT_TIME, function () use ($worker) {
		$time_now = time();
		foreach ($worker->connections as $connection) {
			// 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
			if (empty($connection->lastMessageTime)) {
				$connection->lastMessageTime = $time_now;
				continue;
			}
			// 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
			if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
				$connection->close();
			}
		}
	});
};

// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function ($connection, $data) use ($worker) {
	// 判断当前客户端是否已经验证,既是否设置了uid
	if (!isset($connection->uid)) {

		// 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
		$connection->uid = $connection->id;
		$worker->uidConnections[$connection->uid] = $connection;

		//return $connection->send('login success, your uid is ' . $connection->uid);
		// return sendMessageByUid($connection->uid, '');
	}
    Http::header('Access-Control-Allow-Origin:*');
    Http::header('Access-Control-Allow-Methods: GET, POST');//PUT, DELETE, HEAD, OPTIONS
    Http::header('Cache-Control: no-cache');
	//Http::input(json_encode($data), $connection);
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
				$info = $data;
			} else {
				$info = get_return_date(404, 'Resource not found');
			}
		} else {
			$info = get_return_date(404, 'Resource not found');
			if ($uri[0] === 'favicon.ico'){
				$info = get_return_date(200);
				unset($info['msg']);
			}
		}
	} else {
		$info = get_return_date(200);
	}

	Http::setcookie('botSession', session_id(), 3600, '/', '');
    Http::header('Content-Type:application/json;charset=utf-8');
	Http::header("HTTP/1.1 " . $info['code'] . " " . get_http_status_message($info['code']) . "\r\n\r\n", true, $info['code']);
    unset($info['code']);
	sendMessageByUid($connection->uid, json_encode($info));

};
//
//$worker->onConnect = function ($connection) use($worker){
//	$connection->send('Connect success.');
//};
// 当有客户端连接断开时
$worker->onClose = function ($connection) use ($worker) {
	global $worker;
	if (isset($connection->uid)) {
		// 连接断开时删除映射
		unset($worker->uidConnections[$connection->uid]);
		echo "Client ID " . $connection->uid .' Closed' . PHP_EOL;
	}
};

$worker->onError = function ($connection, $code, $msg) {
	echo "error $code $msg\n";
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



// 运行worker
Worker::runAll();
