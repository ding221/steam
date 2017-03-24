<?php
namespace App\Http;

use Lib\Steam;

class Item {
	public function inventory() {
		$steamid = _get('steamid', '');
		$game_id = _get('game_id', '');
		if ($steamid == '' && $steamid < 1) {
			return get_error_return(422, 'Field missing', 'steamid', 'required');
		}

		if ($game_id != '' && $game_id < 1) {
			return get_error_return(422, 'Field missing', 'game_id', 'required');
		}

		$language = (isset($_GET['language']) && $_GET['language']) ? $_GET['language'] : 'en';
		$steam = new Steam($steamid);
		$inventory_link = $steam->getInventory($game_id, $language);
		$items = curl_get($inventory_link);
		return get_return_date(200, $items);
	}

	public function transaction(){
		$from = _get('from', '');
		$from_item = _get('from_item', '');
		$to = _get('to', '');
		$to_item = _get('to_item', '');
	}

}