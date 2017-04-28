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
		$uname = _get('username', '');
		$pwd = _get('password', '');
		$tfcode = _get('twofactorcode', '');
		$login_url = 'https://store.steampowered.com/login/getrsakey/'; //post
		if (!isset($cfg['username']) && !$cfg['username']) {
			return false;
		}

		if ($uname)
		    $cfg['username'] = $uname;
        if ($pwd)
            $cfg['password'] = $pwd;
        if ($tfcode)
            $cfg['twofactorcode'] = $tfcode;

		$login = https_post($login_url, $cfg, 1);
		return self::doLogin($login, null);
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
				'twofactorcode' => isset($cfg['twofactorcode']) ? $cfg['twofactorcode'] : '', //邮箱or令牌验证码
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
		$login = https_post($do_logion_url, $data, 1, null); //先请求一次，让steam发送验证码
		global $cookie_file;
		$cookie_file = true;
		$login_result = json_decode($login);
		if ($login_result->success) {
			if ($login_result->login_complete) {
				println('Login success!');
				@$transfer_parameters = [
					'steamid' => $login_result->transfer_parameters->steamid,
					'token' => $login_result->transfer_parameters->token,
					'auth' => $login_result->transfer_parameters->auth,
					'remember_login' => $login_result->transfer_parameters->remember_login ? $login_result->transfer_parameters->remember_login : 'false',
					'webcookie' => $login_result->transfer_parameters->webcookie,
					'token_secure' => $login_result->transfer_parameters->token_secure,
				];
				global $cookie_info;
				global $userinfo;
				https_post($login_result->transfer_urls[0], $transfer_parameters, true, null, $transfer_parameters['steamid']);
				$cookie_info = get_file_cookie($transfer_parameters['steamid']);
				$web_url = 'http://store.steampowered.com/';
				$cookie = array_merge($cookie_info, get_cookie($web_url, $transfer_parameters['steamid']));
				$cookie_info = join('; ', $cookie) . '; '; //fruit=apple;colour=red
				foreach ($transfer_parameters as $idx => $value) {
					$cookie_info .= $idx . '=' . $value . '; ';
                }
				$cookie_info = rtrim($cookie_info, '; ');
				$userinfo = $transfer_parameters;
				//把登录信息放入文件中
				file_put_contents('.'.DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . $login_result->transfer_parameters->steamid .'.json', json_encode($userinfo));
				return get_return_date(200, 'Login success.');
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
						self::doLogin($login_info, $data);
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
		return false;
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
					if (isset($files['type']) && $files['type'] == 0) {//0为邮件内容图片,1 为附件
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
