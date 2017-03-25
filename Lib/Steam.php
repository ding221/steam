<?php
namespace Lib;

class Steam {
	/**
	 * Steam 的语言
	 * @var string
	 */
	static protected $language;
	static protected $country;
	static protected $currency;

	public function __construct() {

	}
	/**
	 * 设置为steam的语言
	 * @param $language
	 */
	static protected function setLanguage($language) {
		switch ($language) {
		case 'en':
			self::$language = 'english';
			break;
		case 'zh':
			self::$language = 'schinese';
			break;
		case 'ja':
			self::$language = 'japanese';
			break;
		case 'th':
			self::$language = 'thai';
			break;
		case 'zh-tw':
			self::$language = 'tchinese';
			break;
		case 'pt':
			self::$language = 'portuguese';
			break;
		case 'ru':
			self::$language = 'russian';
			break;
		case 'tr':
			self::$language = 'turkish';
			break;
		case 'it':
			self::$language = 'italian';
			break;
		case 'nl':
			self::$language = 'dutch';
			break;
		case 'fr':
			self::$language = 'french';
			break;
		case 'es':
			self::$language = 'spanish';
			break;
		case 'de':
			self::$language = 'german';
			break;
		case 'kr':
			self::$language = 'koreana';
			break;
		default:
			self::$language = 'english';
			break;
		}
	}

	/**
	 * 获取个人库存
	 *
	 * @param int $game_id
	 * @param string $language
	 * @return string 一个获取库存的连接
	 */
	static public function getInventory($steamid, $game_id, $language = 'en') {
		//730->csgo  570->dota
		self::setLanguage($language);
		//$url2 = 'http://steamcommunity.com/inventory/76561198003709290/730/2?l=schinese&count=75';
		return $url = 'http://steamcommunity.com/inventory/' .
		$steamid . '/' . $game_id . '/2?l=' . self::$language . '&count=75';
	}

	/**
	 * 获取steam市场所有商品
	 *
	 * @param int $currency
	 * @param string $language
	 * @param int $count 每次获取商场的商品数量
	 * @return string
	 */
	static public function getMarket($currency = 0, $count = 20, $language = '') {
		$currency = isset($currency) && $currency != '' ? $currency : self::$currency;
		$language = isset($language) && $language != '' ? $language : self::$language;
		//$url = 'http://steamcommunity.com/market/popular?country=CN&language=schinese&currency=23&count=20';
		return $url = 'http://steamcommunity.com/market/popular?country=' . self::country
		. '&language=' . self::$language
		. '&currency=' . self::$currency
			. '&count=' . $count;

	}

	/**
	 * 获取交易url
	 * @param string $steamid 
	 * @return string
	 */
	static public function getTradeUrl($steamid) {
		return $url = 'https://steamcommunity.com/profiles/'
			. $steamid . '/tradeoffers/privacy#trade_offer_access_url';
	}


	/*
		查询对方库存
		https://steamcommunity.com/tradeoffer/new/partnerinventory/?sessionid=a876df578bc491c501facc17&partner=76561198003709290&appid=730&contextid=2

		https://steamcommunity.com/tradeoffer/new/partnerinventory/?sessionid=a876df578bc491c501facc17&partner=76561198003709290&appid=570&contextid=2

	 */



	/**
	 * 发送交易报价 
	 * @param  string $to_steamid  [description]
	 * @param  array  $trade_info  [description]
	 * @param  array  $from_assets [description]
	 * @param  array  $to_assets   [description]
	 * @return string $tradeofferid          [description]
	 */
	static public function launchTransaction($to_steamid = '', $trade_info = [], $from_assets = [], $to_assets = []) {
		//机器人发送空的交易报价，相当于是客户赠送给机器人的礼物
		global $cookie_info;
		$cookie = explode(';', $cookie_info);
		$sessionid = substr($cookie[0], 10, -1);
		$url = 'https://steamcommunity.com/tradeoffer/new/send';
		$data = [
			'sessionid' => $sessionid, //交易发起人的sessionid
			'serverid' => $trade_info['serverid'], //服务器ID
			"partner" => $to_steamid, //接受交易报价人的steamid
			"tradeoffermessage" => '',
			'json_tradeoffer' => [
				'newversion' => $trade_info['newversion'], //三次测试都为 true
				'version' => $trade_info['version'], //一个未知的版本，目前有2、3两种
				"me" => [ //交易发起人
					"assets" => [],
					"currency" => $trade_info['me_currency'], //交易报价所用的货币，bot 发起的交易货币应该为空，用 kaleoz 的货币来交易
					"ready" => $trade_info['me_ready'], false, //什么鬼 ,猜测是交易报价是否已经被读取的选项
				],
				"them" => [
					"assets" => [
						// "appid" => $game_id, //game_id
						// "contextid" => "2",
						// "amount" => $amount, // 数量
						// "assetid" => $item_id, //(商品ID)8042779318
					],
					"currency" => $trade_info['them_currency'],
					"ready" => $trade_info['them_currency'], //什么鬼？？？ ,猜测是交易报价是否已经被读取的选项
				],
			],
			"captcha" => "",
			"trade_offer_create_params" => [
				//trade url 后面的 token 值 ，有空和不为空两种情况   ？？？
				"trade_offer_access_token" => $trade_info["trade_offer_access_token"], 
			],
		];

		if (is_array($from_assets) && count($from_assets) > 0) {
			foreach ($from_assets as $item) {
				$data["json_tradeoffer"]["me"]["assets"][]['appid'] = $item['game_id'];
				$data["json_tradeoffer"]["me"]["assets"][]['contextid'] = $item['contextid'];
				$data["json_tradeoffer"]["me"]["assets"][]['amount'] = $item['amount'];
				$data["json_tradeoffer"]["me"]["assets"][]['assetid'] = $item['assetid'];
			}
		}

		if (is_array($to_assets) && count($to_assets) > 0) {
			foreach ($to_assets as $item) {
				$data["json_tradeoffer"]["them"]["assets"][]['appid'] = $item['game_id'];
				$data["json_tradeoffer"]["them"]["assets"][]['contextid'] = $item['contextid'];
				$data["json_tradeoffer"]["them"]["assets"][]['amount'] = $item['amount'];
				$data["json_tradeoffer"]["them"]["assets"][]['assetid'] = $item['assetid'];
			}
		}

		return $tradeofferid = https_post($url, $data, 60, 1); //需要https访问

	}

	/**
	 * 取消已经发送的交易报价
	 * 次出应该用 bot 的sessionid 去取消交易报价，因为交易报价都是 bot 主动发起
	 * @param  string $tradeofferid 
	 * @return string $tradeofferid
	 */
	public static function cancelTransaction($tradeofferid){
		global $cookie_info;
		$cookie = explode(';', $cookie_info);
		$sessionid = substr($cookie[0], 10, -1);
		$url = 'https://steamcommunity.com/tradeoffer/1967346842/cancel';
		$data = [
			'sessionid' => 
		];

		return $tradeofferid = https_post($url, $data, 60, 1); //返回取消报价的 tradeofferid
	}

}
