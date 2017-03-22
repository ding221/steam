<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
// 自动加载类
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'func.php';

//心跳间隔
define('HEARTBEAT_TIME', 25);
$worker = new Worker('http://0.0.0.0:8686');
$worker->name = 'SteamWorker';
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
$worker->onWorkerStart = function($worker)
{
    Timer::add(HEARTBEAT_TIME, function()use($worker){
        $time_now = time();
        foreach($worker->connections as $connection) {
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
$worker->onMessage = function($connection, $data) use($worker)
{
    //// 判断当前客户端是否已经验证,既是否设置了uid
    if(!isset($connection->uid))
    {
        $connection->send('Please Login.');
    
        // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
        $connection->uid = $data;
        /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
         * 实现针对特定uid推送数据
         */
        $worker->uidConnections[$connection->uid] = $connection;
        //return $connection->send('login success, your uid is ' . $connection->uid);
        return sendMessageByUid($connection->uid, '');
    }
    //
    //$uri = ltrim($_SERVER["REQUEST_URI"], '/');
    //$uri = strstr($uri, '?', true);
    //$uri = explode('/', $uri);
    //
    //$c = 'App\\Http\\';//加载控制器的命名空间
    //$c .= ucfirst($uri[0]);
    //$a = $uri[1];
    //$o = new $c;
    //$o->$a();
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

//function is_login($connection){
//    global $worker;
//    if (isset($worker->uidConnections[])){
//
//    }
//}

// 运行worker
Worker::runAll();
