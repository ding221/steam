<?php
//调用方法
//首先更新composer
//用中国区镜像 https://pkg.phpcomposer.com/
//php bot.php username password 认证码
//不带认证码执行可以让系统发送认证码到邮箱或手机，再次带认证码执行即可登录
//这里要有收取邮件的功能，通过pop3或者imap去收取邮件，获取认证码

require __DIR__ . '/vendor/autoload.php';
require 'func.php';

define('CRYPT_RSA_PKCS15_COMPAT', true); // May not be necessary, but never hurts to be sure.

const RSA_URL = 'https://steamcommunity.com/login/getrsakey/?username=';
const LOGIN_URL = 'https://store.steampowered.com/login/dologin';

$username = $argc >= 2 ? $argv[1] : '';
$password = $argc >= 3 ? $argv[2] : '';
$twofactorcode = $argc >= 4 ? $argv[3] : '';

$postfields = [
	'username' => $username,
	'password' => '123446',
	'captchagid' => '-1',
	'rsatimestamp' => '342233350000',
	'remember_login' => 'true',
];

$ch = curl_init(RSA_URL . $username);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$resp = curl_exec($ch);
curl_close($ch);

$rsa_result = json_decode($resp);

//printf('RSA KEY: %s', $rsa_result->publickey_mod);

$rsa = new phpseclib\Crypt\RSA();
$rsa->setEncryptionMode(phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
$key = [
	'n' => new phpseclib\Math\BigInteger($rsa_result->publickey_mod, 16),
	'e' => new phpseclib\Math\BigInteger($rsa_result->publickey_exp, 16), // Fixed base :)
];
$rsa->loadKey($key);
$password = base64_encode($rsa->encrypt($password)); // Steam uses Base64_Encode()

//printf("Encrypt PWD: %s", $password);

$postfields = [
	'password' => $password,
	'username' => $username,
	'twofactorcode' => $twofactorcode,
	'emailauth' => '',
	'loginfriendlyname' => '',
	'captchagid' => '-1', // If all goes well, you shouldn't need to worry
	'captcha_text' => '', // about Captcha.
	'emailsteamid' => '',
	'rsatimestamp' => $rsa_result->timestamp,
	'remember_login' => 'false',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, LOGIN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
$resp = curl_exec($ch);

//需要验证码 {"success":false,"requires_twofactor":true,"message":""}
//密码错误 {"success":false,"requires_twofactor":false,"message":"The account name or password that you have entered is incorrect.","clear_password_field":true,"captcha_needed":false,"captcha_gid":-1}
//成功 {"success":true,"requires_twofactor":false,"login_complete":true,"transfer_urls":["https:\/\/steamcommunity.com\/login\/transfer","https:\/\/help.steampowered.com\/login\/transfer"],"transfer_parameters":{"steamid":"76561198003709290","token":"60F4F860BBB6449C27F93543B74E507235D3FBEC","auth":"631f3ccf0966f8d7fc98b9b82b592b38","remember_login":false,"webcookie":"B7B88B082FEC44500D20384E3096E9905ABA9BE8","token_secure":"E4B33FC99A88AC62D779A345D21EC2D773491AB6"}}
$login_result = json_decode($resp);
if ($login_result->success) {
	if ($login_result->login_complete) {
		println('login!');
	}
} else {
	if ($login_result->requires_twofactor) {
		println('need captch');
	} else {
		if (isset($login_result->message)) {
			println($login_result->message);
		} else {
			println('fail');
		}
	}
}