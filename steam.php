<?php

define('APP', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('DIR', dirname(__FILE__));
define('FUNC', APP . 'common' . DIRECTORY_SEPARATOR);
define('LIB', APP . 'lib' . DIRECTORY_SEPARATOR);
define('ACT', APP . 'act' . DIRECTORY_SEPARATOR);
define('CFG', APP . 'conf' . DIRECTORY_SEPARATOR);
define('LOG', APP . 'log' . DIRECTORY_SEPARATOR);
define('COOKIE', APP . 'cookie.json');
$cookie_info = '';
$userinfo = '';
try{
    
    require_once CFG . 'cfg.php';
    //require FUNC. 'func.php';
    require_once LIB. 'Load.php';
    require_once './vendor/autoload.php';

    spl_autoload_register("\\AutoLoading\\load::autoload");

    Lib\Controller::run();
    
    
    
} catch (Exception $e){
    echo 'Error: ' . $e->getCode();
    echo 'Line: ' . $e->getLine();
}

