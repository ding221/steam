<?php
namespace Lib;

use Act;
class Controller {
    public $c;
    public $a;

    private function __construct()
    {
    }

    public static function run()
    {
        $c = 'Act\\';
        $c .= isset($_GET['c']) ? $_GET['c'] : "login"; //url提供类名字的变量名
        $a = isset($_GET['a']) ? $_GET['a'] : "login"; //url提供方法名字的变量名

        $a .= 'Action';
        if( class_exists($c) && method_exists($c, $a) ) {
            $o = new $c();
            $o->$a() or $o::$a();
        }else{
            echo "error";
            exit();
        }
    }
}