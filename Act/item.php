<?php
namespace Act;

use Lib\Steam;
class item{
    //http://steamcommunity.com/market/pricehistory/?appid=730&market_hash_name=SG%20553%20%7C%20Waves%20Perforated%20(Battle-Scarred)

    static public function setPrice(){
        //$url = http://steamcommunity.com/market/pricehistory/?appid=730&market_hash_name=SG%20553%20%7C%20Waves%20Perforated%20(Battle-Scarred);
        //$data = ['appid' => 730, 'market_hash_name'=> 'SG 553 | Waves Perforated (Battle-Scarred)'];
    }

    static public function item(){
        $cookie = get_login_info();
        return self::getInventory($cookie['steamid'], $appid = 570);
    }
    
    static public function getInventory($steam_id, $appid){
        if (!$steam_id) {
            return false;   
        }
        if (isset($steam_id) && $steam_id){
            $steam = new Steam($steam_id);
            $Inventory_url = $steam->getInventory($appid, $language = 'zh');
        } else {
            println('Please Login');
            return false;
        }
        $item = curlGet($Inventory_url);
        var_dump($item);
        die();

    }


}