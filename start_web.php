<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
// 自动加载类
require_once __DIR__ . './vendor/autoload.php';

//心跳间隔
define('HEARTBEAT_TIME', 25);
$worker = new Worker('http://0.0.0.0:8686');
$worker->name = 'SteamWorker';
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
$worker->onWorkerStart = function($worker)
{

    //require_once 'steam.php';
    //global $cookie_info;
    //global $userinfo;
    //if ($cookie_info != ''){
    //    Timer::add(HEARTBEAT_TIME, 'send_cookie', array($cookie_info, $userinfo, HEARTBEAT_TIME));
    //}
};

// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function($connection, $data) use($worker)
{
    //require_once 'steam.php';
    //global $cookie_info;
    //global $userinfo;
    //echo $userinfo . PHP_EOL;
    //if ($cookie_info != ''){
    //    Timer::add(HEARTBEAT_TIME, 'send_cookie', array($cookie_info, $userinfo, HEARTBEAT_TIME));
    //}

    $connection->send('Connect success.Please Login.');
};

// 当有客户端连接断开时
$worker->onClose = function($connection)use($worker)
{
    global $worker;
    if(isset($connection->uid))
    {
        // 连接断开时删除映射
        unset($worker->uidConnections[$connection->uid]);
    }
};

// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if(isset($worker->uidConnections[$uid]))
    {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
        return true;
    }
    return false;
}

function send_cookie($cookie_info, $userinfo, $timeout){
    if (!$cookie_info)
        return false;
    if (!$userinfo)
        return false;
    $web_url = 'http://steamcommunity.com/profiles/' . $userinfo['steamid'] .'/home';
    https_post($web_url, $cookie_info, $timeout);
}

// 运行worker
Worker::runAll();
