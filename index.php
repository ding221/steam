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
set_time_limit(0);
try{
    require_once CFG . 'cfg.php';
    require FUNC. 'func.php';
    require LIB. 'Load.php';
    require 'vendor/autoload.php';

    spl_autoload_register("\\AutoLoading\\load::autoload");

    lib\Controller::run();
} catch (Exception $e){
    echo 'Error: ' . $e->getMessage();
}

