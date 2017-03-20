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
set_time_limit(0);
try{
    require_once CFG . 'cfg.php';
    require FUNC. 'func.php';
    require LIB. 'Load.php';
    require 'vendor/autoload.php';

    spl_autoload_register("\\AutoLoading\\load::autoload");

    //lib\Controller::run();

    $host = $cfg['email_host'];
    $user = $cfg['email'];
    $pass = $cfg['email_pwd'];
    $port = $cfg['port'];
    $ssl = $cfg['ssl'];
    $stmp_type = $cfg['stmp_type'];

    $mail = new \Lib\Mail($user, $pass, $host, $port, $ssl, $stmp_type);
    $conn = $mail->connect();
    echo 5/0;
    var_dump($mail);
    var_dump($conn);

} catch (Exception $e){
    echo 'Error: ' . $e->getCode();
    echo 'Line: ' . $e->getLine();
}

