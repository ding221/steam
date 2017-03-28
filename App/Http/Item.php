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

		if ($game_id != '' && $game_id < 1) {
			return get_error_return(422, 'missing required parameter', 'game_id', 'required');
		}

		$language = (isset($_GET['language']) && $_GET['language']) ? $_GET['language'] : 'en';

		$inventory_link = Steam::getInventory($game_id, $language);
		$items = curl_get($inventory_link);
		return get_return_date(200, $items);
	}

	public function sendTransaction() {
		//$from_item = _post('from_item', []);
		$tradeoffermessage = trim(_post('tradeoffermessage', ''));
		$to_steamid = _post('to', '');
		$to_item = _post('to_item', []);
		$token = trim(_post('token', ''));

		$trade_info = [
			'serverid' => '1', // ???
			'tradeoffermessage' => $tradeoffermessage, //发送报价时的留言
			'newversion' => true, // ???
			'version' => '2', // ???
			'me_currency' => [],
			'me_ready' => false, // ???
			'them_currency' => [],
			'them_ready' => false, // ???

			//https://steamcommunity.com/tradeoffer/new/?partner=43443562&token=TkzH4VYR
			'trade_offer_access_token' => $token, //从steam设置的交易链接中获取token值
		];

		//bot 发出报价时应该空
		$from_assets = [

		];

		$to_assets = [];
		//$to_assets = [
		//	[
		//		"appid" => $to_item['game_id'], //game_id
		//		"contextid" => "2", //测试3次值都为2
		//		"amount" => $to_item['amount'], // 数量
		//		"assetid" => $to_item['item_id'], //(商品ID)8042779318
		//	],
		//];
		foreach ($to_item as $item) {
			$to_assets = [
				[
					'appid' => $item['appid'],
					"contextid" => $item['contextid'],
					"amount" => $item['amount'],
					"assetid" => $item['assetid'],
				]
			];
		}
		
		$trade_info = Steam::launchTransaction($to_steamid, $trade_info, $from_assets, $to_assets);
		//可能的结果1：{"tradeofferid":"1974096885","needs_mobile_confirmation":true,"needs_email_confirmation":false,"email_domain":"qq.com"}
		//可能的结果2：{"tradeofferid":"1974096885"}
		//可能的结果3：？？？
		print_r($trade_info);
		//if ($tradeoffid > 1) {
			return get_return_date(200, $trade_info);
		//}

	}

	public function cancelTransaction() {
		$tradeofferid = _get('tradeofferid', 0);
		if ($tradeofferid < 1) {
			return get_error_return(422, "missing required parameter", "tradeofferid", "required");
		}

		$_tradeofferid = Steam::launchTransaction($tradeofferid);
		if ($_tradeofferid < 1) {
			return get_return_date(200, "Error");
		}

		return get_return_date(200, ['tradeofferid' => $_tradeofferid]);
	}

}