<?php
namespace App\Http;

use Lib\Mail;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class User {

	protected static $con_email_times = 0;
	protected static $receive_email_times = 0;

	public static function login() {
		global $cfg;
		$login_url = 'https://store.steampowered.com/login/getrsakey/'; //post
		if (!isset($cfg['username']) && !$cfg['username']) {
			return false;
		}

		$login = https_post($login_url, $cfg);
		self::doLogin($login);
	}

	protected static function doLogin($login_info = null, $data = []) {
		global $cfg;
		if (!count($data)) {
			$rsa_result = json_decode($login_info);
			$rsa = new RSA();
			$rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
			$key = [
				'n' => new BigInteger($rsa_result->publickey_mod, 16),
				'e' => new BigInteger($rsa_result->publickey_exp, 16), //Fixed base :)
			];
			$rsa->loadKey($key);
			$password = base64_encode($rsa->encrypt($cfg['password'])); //Steam uses Base64_Encode()
			$data = [
				'username' => $cfg['username'],
				'password' => $password,
				'twofactorcode' => 'rc3yw', //邮箱or令牌验证码
				'emailauth' => '',
				'loginfriendlyname' => '',
				'captchagid' => -1,
				'captcha_text' => '', //判断机器人验证码
				'emailsteamid' => '',
				'rsatimestamp' => $rsa_result->timestamp,
				'remember_login' => "false", //只有启用令牌才能true
			];
		}

		$do_logion_url = 'https://store.steampowered.com/login/dologin/'; //post
		$login = https_post($do_logion_url, $data); //先请求一次，让steam发送验证码

		$login_result = json_decode($login);
		if ($login_result->success) {
			//{"success":true,"requires_twofactor":false,"login_complete":true,
			//"transfer_urls":["https:\/\/steamcommunity.com\/login\/transfer","https:\/\/help.steampowered.com\/login\/transfer"],
			//"transfer_parameters":{"steamid":"76561198138552821","token":"1F8EB0566BF76A734B378061DA2EAFDCDCAC54AB","auth":"c18eab7dd3805f7c955ebf9442858a41",
			//"remember_login":false,"webcookie":"84246A4FC0ADBA1717718C1A2AC75DC4E2E9D12E","token_secure":"97F7544C456205FEB47FDBA69F72FECEE8D1639D"}}
			if ($login_result->login_complete) {
				$transfer_parameters = [
					'steamid' => $login_result->transfer_parameters->steamid,
					'token' => $login_result->transfer_parameters->token,
					'auth' => $login_result->transfer_parameters->auth,
					'remember_login' => $login_result->transfer_parameters->remember_login,
					'webcookie' => $login_result->transfer_parameters->webcookie,
					'token_secure' => $login_result->transfer_parameters->token_secure,
				];
				println('Login success!');
				$web_url = 'http://steamcommunity.com/profiles/' . $login_result->transfer_parameters->steamid . '/home';
				$cookie = get_cookie($web_url);
				global $cookie_info;
				global $userinfo;
				$cookie_info = join(';', $cookie); //fruit=apple;colour=red
				$userinfo = $transfer_parameters;
				//把登录信息放入文件中
				file_put_contents('./steaminfo.json', json_encode($userinfo));
				return false;
			}
		} else {
			if ($login_result->requires_twofactor) {
				println("Receive E-mail and Captcha");
				sleep(30); //30s后再去邮箱获得验证码
				$body = self::receiveEmail();
				if ($body) {
					$captcha = self::getCaptcha($body);
					if ($captcha) {
						$data['twofactorcode'] = $captcha;
					}

				}
			} else {
				if (isset($login_result->message)) {
					println($login_result->message);
					return false;
				} else {
					println('Failed');
					return false;
				}
			}
		}
	}

	public static function receiveEmail() {
		global $cfg;
		//noreply@steampowered.com  steam发送验证码的邮箱账号
		//$emails = imap_search($mbox, 'FROM "noreply@steampowered.com"', SE_UID);
		//Your Steam account: Access from new web or mobile device
		//$some   = imap_search($conn, 'SUBJECT "Your Steam account: Access from new web or mobile device"', SE_UID);
		$host = $cfg['email_host'];
		$user = $cfg['email'];
		$pass = $cfg['email_pwd'];
		$port = $cfg['port'];
		$ssl = $cfg['ssl'];
		$stmp_type = $cfg['stmp_type'];

		//$mail = new Mail($user, $pass, $host, '993', true, 'imap');
		$mail = new Mail($user, $pass, $host, $port, $ssl, $stmp_type);
		$conn = $mail->connect();
		if (!$conn) {
			println("Connect to email server failed.Try to reconnect.");
			self::$con_email_times++;
			if (self::$con_email_times > 3) {
				println("Connect to email server failed.Try to log in again");
				self::$con_email_times = 0;
				return false;
			} else {
				sleep(30); //等待30s 避免服务器关闭连接
				return self::receiveEmail();
			}
		} else {
			$img = '/img';
			$savePath = 'download/' . date('Ym/');
			if (!file_exists($savePath)) {
				@mkdir($savePath, 0777, true);
				touch($savePath . 'index.html');
			}
			$savePath = dirname($savePath) . '/';
			$tot = $mail->getTotalMails(); //总共收到邮件

			if ($tot < 1) {
				println('No mail');
				die();
			} else {
				$res = [];
				//for($i=$tot;$i>0;$i--){
				$i = 1;
				$head = $mail->getHeaders($i);
				$files = $mail->GetAttach($i, $savePath); //获取邮件附件，返回的邮件附件信息数组
				$imageList = [];
				foreach ($files as $k => $file) {
					if (isset($files['type']) && $files['type'] == 0) {
//0为邮件内容图片,1 为附件
						$imageList[$file['title']] = $file['pathname'];
					}
				}
				$body = $mail->getBody($i, $img, $imageList);
				//$res['mail'][]=array('body'=>$body);
				//}
				$mail->close_mailbox();
				return $body;
			}
		}
	}

	protected static function getCaptcha($body) {
		$regex = "/<div.*?>.*?<\/div>/ism";
		$regex2 = '/<span.*>(.*)<\/span>/isU';
		$matches = [];
		preg_match_all($regex, $body, $matches, PREG_PATTERN_ORDER);
		preg_match($regex2, $matches[0][0], $arr);
		$captch = $arr[1];
		return $captch;
	}
}