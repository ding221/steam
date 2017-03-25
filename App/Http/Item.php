<?php
namespace App\Http;

use Lib\Steam;

class Item {
	public function inventory() {
		$steamid = _get('steamid', '');
		$game_id = _get('game_id', '');
		if ($steamid == '' && $steamid < 1) {
			return get_error_return(422, 'Validation Failed', 'steamid', 'required');
		}

		if ($game_id != '' && $game_id < 1) {
			return get_error_return(422, 'Validation Failed', 'game_id', 'required');
		}

		$language = (isset($_GET['language']) && $_GET['language']) ? $_GET['language'] : 'en';

		$inventory_link = Steam::getInventory($game_id, $language);
		$items = curl_get($inventory_link);
		return get_return_date(200, $items);
	}

	public function sendTransaction() {
		$from = _get('from', '');
		$from_item = _get('from_item', []);
		$to_steamid = _get('to', '');
		$to_item = _get('to_item', []);
		$trade_info = [
			'serverid' => '',
			'newversion' => '',
			'version' => '',
			'me_currency' => [],
			'me_ready' => false, //次出存疑
			'them_currency' => [],
			'them_ready' => false, //次出存疑
			'trade_offer_access_token' => '',
		];

		//bot 发出报价时应该空
		$from_assets = [

		];	

		$to_assets = [
			[
				"appid" => $game_id, //game_id
				"contextid" => "2", //测试3次值都为2
				"amount" => $amount, // 数量
				"assetid" => $item_id, //(商品ID)8042779318
			],
		];
	}

	public function cancelTransaction(){
		$tradeofferid = _get('tradeofferid', 0);
		if ($tradeofferid < 1) {
			return get_error_return(422, "Validation Failed"， "tradeofferid", "required");
		}

		$_tradeofferid = Steam::launchTransaction($tradeofferid);
		if ($_tradeofferid < 1) {
			return get_return_date(200, "Error");
		}

		return get_return_date(200, ['tradeofferid' => $_tradeofferid]);
	}

}