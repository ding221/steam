<?php
/**
 * 入口文件应负责文件的调用和路由的转发
 */
define('APP', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('DIR', dirname(__FILE__));
define('FUNC', APP . 'common' . DIRECTORY_SEPARATOR);
define('LIB', APP . 'lib' . DIRECTORY_SEPARATOR);
define('ACT', APP . 'act' . DIRECTORY_SEPARATOR);
define('CFG', APP . 'conf' . DIRECTORY_SEPARATOR);
define('LOG', APP . 'log' . DIRECTORY_SEPARATOR);
define('COOKIE', APP . 'cookie.json');
set_time_limit(0);
try{
    require_once CFG . 'cfg.php';
    //require FUNC. 'func.php';
    require LIB. 'Load.php';
    require 'vendor/autoload.php';

    spl_autoload_register("\\AutoLoading\\load::autoload");

    Lib\Controller::run();
    //php -S localhost:8070 index.php
    
    
//$host = 'imap.qq.com';
//$user = '471240186@qq.com';
//$pass = 'xdvbaxgxjvyzcaii';
//
//$mail = new Lib\Mail($user, $pass, $host, '993', true, 'imap');
//$conn = $mail->connect();
//if (!$conn){
//    return '链接失败';
//}
//$img = '/img';
//$savePath = 'upload/' . date('Ym/');
//if(!file_exists($savePath)) {
//    @mkdir($savePath, 0777, true);
//    touch($savePath . 'index.html');
//}
//$savePath = dirname($savePath) . '/';
//$tot = $mail->getTotalMails();//总共收到邮件
//
//if($tot < 1){
//    echo '没有邮件';
//    die();
//} else {
//    $res = [];
//    for($i=$tot;$i>0;$i--){
//        //$i = 5;
//        $head = $mail->getHeaders($i);
//        $files = $mail->GetAttach($i, $savePath);//获取邮件附件，返回的邮件附件信息数组
//        $imageList = [];
//        foreach ($files as $k => $file){
//            if (isset($files['type']) && $files['type'] == 0){//0为邮件内容图片,1 为附件
//                $imageList[$file['title']] = $file['pathname'];
//            }
//        }
//        $body = $mail->getBody($i, $img, $imageList);
//        //$res['mail'][]=array('body'=>$body);
//    }
//    $mail->close_mailbox();
//}
//
//    var_dump($mail);
//    var_dump($conn);
//    var_dump($body);







} catch (Exception $e){
    echo 'Error: ' . $e->getCode();
    echo 'Line: ' . $e->getLine();
}

