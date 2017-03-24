<?php
namespace Lib;

class Steam {
	protected $g_steamID = null;
	protected $language;
	protected $country;
	protected $currency;

	//(购买链接,创建订单) response : {success: 1, buy_orderid: "1063850253"}
	//https://steamcommunity.com/market/createbuyorder/
	//post 数据:
	/*sessionid=8fb27cf515d1ce93e8d05437&currency=23&appid=753&market_hash_name=496400-Cemetery+(Profile+Background)&price_total=50&quantity=1*/
	/*sessionid:8fb27cf515d1ce93e8d05437
		currency:23
		appid:753
		market_hash_name:496400-Cemetery (Profile Background)
		price_total:50
	*/

	//http://steamcommunity.com/market/getbuyorderstatus/?sessionid=8fb27cf515d1ce93e8d05437&buy_orderid=1063850253
	/*
	 * response : {"success":1,"active":1,"purchased":0,"quantity":"1","quantity_remaining":"1","purchases":[]}
	 * */

	
	//http://steamcommunity.com/market/itemordershistogram?country=CN&language=schinese&currency=23&item_nameid=175875625&two_factor=0//(此链接查询是否有人在销售此物品)
	/*
	 * response: {"success":1,"sell_order_table":"","sell_order_summary":"\u76ee\u524d\u6ca1\u6709\u4eba\u51fa\u552e\u6b64\u7269\u54c1\u3002","buy_order_table":"<table class=\"market_commodity_orders_table\"><tr><th align=\"right\">\u4ef7\u683c<\/th><th align=\"right\">\u6570\u91cf<\/th><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.50<\/td><td align=\"right\">2<\/td><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.47<\/td><td align=\"right\">6<\/td><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.46<\/td><td align=\"right\">2<\/td><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.41<\/td><td align=\"right\">6<\/td><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.37<\/td><td align=\"right\">5<\/td><\/tr><tr><td align=\"right\" class=\"\">\u00a5 0.36 \u6216\u66f4\u4f4e<\/td><td align=\"right\">13<\/td><\/tr><\/table>","buy_order_summary":"<span class=\"market_commodity_orders_header_promote\">34<\/span> \u4eba\u8bf7\u6c42\u4ee5 <span class=\"market_commodity_orders_header_promote\">\u00a5 0.50<\/span> \u6216\u66f4\u4f4e\u7684\u4ef7\u683c\u8d2d\u4e70","highest_buy_order":"50","lowest_sell_order":null,"buy_order_graph":[[0.5,2,"2 \u4efd \u00a5 0.50 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.47,8,"8 \u4efd \u00a5 0.47 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.46,10,"10 \u4efd \u00a5 0.46 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.41,16,"16 \u4efd \u00a5 0.41 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.37,21,"21 \u4efd \u00a5 0.37 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.36,22,"22 \u4efd \u00a5 0.36 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.32,26,"26 \u4efd \u00a5 0.32 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.3,28,"28 \u4efd \u00a5 0.30 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.29,29,"29 \u4efd \u00a5 0.29 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.14,30,"30 \u4efd \u00a5 0.14 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.12,31,"31 \u4efd \u00a5 0.12 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.09,32,"32 \u4efd \u00a5 0.09 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.08,33,"33 \u4efd \u00a5 0.08 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"],[0.03,34,"34 \u4efd \u00a5 0.03 \u4ee5\u4e0a\u7684\u8ba2\u8d2d\u5355"]],"sell_order_graph":[],"graph_max_y":40,"graph_min_x":0.03,"graph_max_x":0,"price_prefix":"\u00a5","price_suffix":""}
	 *
	 */


	/*
     * 用ajax的方式获取新消息
     * $url = 'http://steamcommunity.com/profiles/' . $userinfo['steamid'] . '/ajaxgetusernews/?start='. time();
     * $abc = curl_get($url);
     */


	//前往steam接收报价
	//https://steamcommunity.com/tradeoffer/1964960963
	//可能需要的参数
	//{"status": 50,
	// "bot_name": "IGXE\u00a0Bot\u00a0#643",
	// "reqid": "31a9b8536acf41689f544258386e62f5",
	// "auth_code": "AB1bsosN",
	// "msg": "\u62a5\u4ef7\u53d1\u9001\u6210\u529f",
	// "trade_offer_id": "1964960963"}

	//assetid  为商品id
	//发送报价 post
	//https://steamcommunity.com/tradeoffer/new/send
	/*
	 * data:
	 * sessionid: "c13597a3de2c49b64a4f5350",
	 * serverid: "1",
	 * partner: "76561198003709290",
	 * tradeoffermessage:"",
	 * json_tradeoffer:"{"newversion":true,"version":2,"me":{"assets":[],"currency":[],"ready":false},"them":{"assets":[{"appid":730,"contextid":"2","amount":1,"assetid":"8042779318"}],"currency":[],"ready":false}}",
	 * captcha:"",
	 * trade_offer_create_params:"{"trade_offer_access_token":"TkzH4VYR"}"
	 *
	 * response:
	 * tradeofferid:"1964980319"
	 *
	 */

	public function __construct($steam_id) {
		if ($steam_id) {
			$this->g_steamID = $steam_id;
		} else {
			return false;
		}
	}
	/**
	 * 设置为steam的语言
	 * @param $language
	 */
	protected function setLanguage($language) {
		switch ($language) {
		case 'en':
			$this->language = 'english';
			break;
		case 'zh':
			$this->language = 'schinese';
			break;
		case 'ja':
			$this->language = 'japanese';
			break;
		case 'th':
			$this->language = 'thai';
			break;
		case 'zh-tw':
			$this->language = 'tchinese';
			break;
		case 'pt':
			$this->language = 'portuguese';
			break;
		case 'ru':
			$this->language = 'russian';
			break;
		case 'tr':
			$this->language = 'turkish';
			break;
		case 'it':
			$this->language = 'italian';
			break;
		case 'nl':
			$this->language = 'dutch';
			break;
		case 'fr':
			$this->language = 'french';
			break;
		case 'es':
			$this->language = 'spanish';
			break;
		case 'de':
			$this->language = 'german';
			break;
		case 'kr':
			$this->language = 'koreana';
			break;
		default:
			$this->language = 'english';
			break;
		}
	}

	/**
	 * 获取个人库存
	 *
	 * @param int $game_id
	 * @param string $language
	 * @return string
	 */
	public function getInventory($game_id, $language = 'en') {
		//730->csgo  570->dota
		$this->setLanguage($language);
		//$url2 = 'http://steamcommunity.com/inventory/76561198003709290/730/2?l=schinese&count=75';
		return $url = 'http://steamcommunity.com/inventory/' .
		$this->g_steamID . '/' . $game_id . '/2?l=' . $this->language . '&count=75';
	}

	/**
	 * 获取市场所有商品
	 *
	 * @param int $currency
	 * @param string $language
	 * @param int $count
	 * @return string
	 */
	public function getMarket($currency = 0, $count = 20, $language = '') {
		$currency = isset($currency) && $currency != '' ? $currency : $this->currency;
		$language = isset($language) && $language != '' ? $language : $this->language;
		//$url = 'http://steamcommunity.com/market/popular?country=CN&language=schinese&currency=23&count=20';
		return $url = 'http://steamcommunity.com/market/popular?country=' . $this->country
			. '&language=' . $language
			. '&currency=' . $currency
			. '&count=' . $count;

	}

	/**
	 * 获取交易url
	 * @return string
	 */
	public function getTradeUrl() {
		return $url = 'https://steamcommunity.com/profiles/'
		. $this->g_steamID . '/tradeoffers/privacy#trade_offer_access_url';
	}

}