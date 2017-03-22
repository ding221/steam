<?php
namespace App\Http;

class User
{
    function token(){
        $username = $_GET['username'];
        $password = $_GET['password'];
        if (!$username){
            echo '账号或密码错误';
        }
        if (!$password)
            echo "账号或密码错误";

        global $worker;
        echo 'Login success!';
    }
}