<?php
namespace Act;

use phpseclib;
use Lib;
class loginController{

    protected static $con_email_times = 0;
    protected static $receive_email_times = 0;


    public static function loginAction(){

        global $cfg;
        $login_url = 'https://store.steampowered.com/login/getrsakey/';//post
        if (!isset($cfg['username']) && !$cfg['username'])
            return false;
        $login = httpsPost($login_url, $cfg);
        self::doLogin($login);
    }
    
    protected static function doLogin($login_info = null, $data = null){
        global $cfg;
        if (!count($data)){
            $rsa_result = json_decode($login_info);
            $rsa = new phpseclib\Crypt\RSA();
            $rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
            $key = [
                'n' => new phpseclib\Math\BigInteger($rsa_result->publickey_mod, 16),
                'e' => new phpseclib\Math\BigInteger($rsa_result->publickey_exp, 16), // Fixed base :)
            ];
            $rsa->loadKey($key);
            $password = base64_encode($rsa->encrypt($cfg['password'])); // Steam uses Base64_Encode()
            $data = [
                'username' => $cfg['username'],
                'password' => $password,
                'twofactorcode' => '',//邮箱验证码
                'emailauth' => '',
                'loginfriendlyname' => '',
                'captchagid' => -1,
                'captcha_text' => '',//判断机器人验证码
                'emailsteamid' => '',
                'rsatimestamp' => $rsa_result->timestamp,
                'remember_login' => "false"//只有启用令牌才能true
            ];
        }

        $do_logion_url = 'https://store.steampowered.com/login/dologin/';//post
        $login = httpsPost($do_logion_url, $data);//先请求一次，让steam发送验证码
        println($login);
        $login = json_decode($login);

        $login_result = json_decode($login);
        if ($login_result->success) {
            if ($login_result->login_complete) {
                println('Login success!');
            }
        } else {
            if ($login_result->requires_twofactor) {
                $body = self::receiveEmail();
                $captcha = self::getCaptcha($body);
                $data['twofactorcode'] = $captcha;

                if ($captcha != '') {
                    self::doLogin('', $data);
                } else {

                }
            } else {
                if (isset($login_result->message)) {
                    println($login_result->message);
                } else {
                    println('fail');
                }
            }
        }

        //if ($login['success'] == false) {
        //    $body = self::receiveEmail();
        //    $captcha = self::getCaptcha($body);
        //    $data['twofactorcode'] = $captcha;
        //    //self::doLogin($login_info);
        //} else {
        //    //其他业务处理
        //}
    }

    protected static function receiveEmail(){
        global $cfg;
        $host = $cfg['email_pop3'];
        $user = $cfg['email'];
        $pass = $cfg['email_pwd'];
        $port = $cfg['port'];
        $ssl = $cfg['ssl'];
        $stmp_type = $cfg['stmp_type'];

        $mail = new Lib\Mail($user, $pass, $host, $port, $ssl, $stmp_type);
        $conn = $mail->connect();
        if (!$conn){
            println("Connect to email server failed.Try to reconnect.");
            self::$con_email_times++;
            if (self::$con_email_times >3){
                println("Connect to email server failed.");
                return false;
            }
            self::receiveEmail();
        }
        $img = '/img';
        $savePath = 'upload/' . date('Ym/');
        if(!file_exists($savePath)) {
            @mkdir($savePath, 0777, true);
            touch($savePath . 'index.html');
        }
        $savePath = dirname($savePath) . '/';
        $tot = $mail->getTotalMails();//总共收到邮件

        if($tot < 1){
            println("Inbox no mail, Wait 10 seconds to query");
            sleep(10);
            self::receiveEmail();
            //return false;
        } else {
            //for($i=$tot;$i>0;$i--){
                //$head = $mail->getHeaders($i);//获取邮件头部信息
                $i = 1;//验证邮件默认为第一封邮件
                $files = $mail->GetAttach($i, $savePath);//获取邮件附件，返回的邮件附件信息数组
                $imageList = [];
                foreach ($files as $k => $file){
                    if (isset($files['type']) && $files['type'] == 0){//0为邮件内容图片,1 为附件
                        $imageList[$file['title']] = $file['pathname'];
                    }
                }
                $body = $mail->getBody($i, $img, $imageList);

            //}
            $mail->close_mailbox();
        }
        if ($body){
            return $body;
        } else {
            self::receiveEmail();
        }

    }

    protected static function getCaptcha($body){
        $regex = "/<div.*?>.*?<\/div>/ism";
        $regex2 = '/<span.*>(.*)<\/span>/isU';
        $matches =[];
        preg_match_all($regex,  $body,  $matches,  PREG_PATTERN_ORDER);
        preg_match($regex2, $matches[0][0], $arr);
        $captch = $arr[1];
        return $captch;
    }
}