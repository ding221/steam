<?php
namespace App\Http;

use Lib\Steam;
use Workerman\Protocols\Http;

class Item {
	public function inventory() {
		$steamid = _get('steamid', '');
		$game_id = _get('game_id', '');
		if ($steamid == '' && $steamid < 1) {
			return get_error_return(422, 'missing required parameter', 'steamid', 'required');
		}

		if ($game_id == '' && $game_id < 1) {
			return get_error_return(422, 'missing required parameter', 'game_id', 'required');
		}

		$language = (isset($_GET['language']) && $_GET['language']) ? $_GET['language'] : 'en';

		$inventory_link = Steam::getInventory($steamid, $game_id, $language);
		$items = curl_get($inventory_link);
		return get_return_date(200, $items);
	}

	public function sendTransaction() {
		$tradeoffermessage = trim(_post('tradeoffermessage', ''));
		$to_steamid = _post('to', '');
		$to_item = _post('to_item', []);
		$token = trim(_post('token', ''));
		if (!$to_steamid) {
			return get_error_return(422, 'missing required parameter', 'steamid', 'required');
		}
		if (count($to_item) < 1) {
			return get_error_return(422, 'missing required parameter', 'to_item', 'required');
		}
		$trade_info = [
			'serverid' => '1', // ???
			'tradeoffermessage' => $tradeoffermessage, //发送报价时的留言
			'newversion' => true, // ???
			'version' => '2', // ???
			'me_currency' => [],
			'me_ready' => false, // ???
			'them_currency' => [],
			'them_ready' => false, // ???
			'trade_offer_access_token' => $token, //从steam设置的交易链接中获取token值
		];

		//bot 发出报价时应该空
		$from_assets = [

		];

		$to_assets = [];
		if (is_string($to_item) && $to_item) {
			$to_item = json_decode($to_item, true);
		}

		if (is_array($to_item) && $to_item) {
			foreach ($to_item as $item) {
				$to_assets = [
					[
						'appid' => $item['appid'],
						"contextid" => $item['contextid'], //dota和csgo的contextid值为2
						"amount" => $item['amount'],
						"assetid" => $item['assetid'],
					],
				];
			}
		} else {
			return get_error_return(415, 'Need an array', 'to_item', 'invalid');
		}

		$trade_info = Steam::launchTransaction($to_steamid, $trade_info, $from_assets, $to_assets);
		//可能的结果1：{"tradeofferid":"1974096885","needs_mobile_confirmation":true,"needs_email_confirmation":false,"email_domain":"qq.com"}
		//可能的结果2：{"tradeofferid":"1974096885"}
		//可能的结果3：？？？
		return get_return_date(200, $trade_info);
	}

	public function getItem() {
		$sessionid = get_cookie_info('sessionid');
		$url = 'https://steamcommunity.com/tradeoffer/new/partnerinventory/?sessionid=' . $sessionid . '&partner=76561198003709290&appid=570&contextid=2';
		$headers[] = 'Accept:"text/javascript, text/html, application/xml, text/xml, */*"';
		$headers[] = 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';
		$headers[] = 'Accept-Encoding:"gzip, deflate, br"';
		$headers[] = 'X-Requested-With:"XMLHttpRequest"';
		$headers[] = 'X-Prototype-Version:"1.7"';
		$data = curl_get($url, true, $headers);
		return get_return_date(200, $data);
	}

	public function getNotifation() {
		global $cookie_info;
		global $userinfo;
		$msg = get_Notification_Counts($cookie_info, $userinfo);
		return get_return_date(200, $msg);
	}

	public function cancelTransaction() {
		$tradeofferid = _get('tradeofferid', 0);
		if ($tradeofferid < 1) {
			return get_error_return(422, "missing required parameter", "tradeofferid", "required");
		}

		$msg = Steam::launchTransaction($tradeofferid);

		return get_return_date(200, $msg);

	}

}