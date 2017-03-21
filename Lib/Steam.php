<?php
namespace Lib;

class Steam {
    protected $g_steamID = null;
    protected $language;
    protected $country;
    protected $currency;

    public function __construct($user)
    {
        if (isset($user['steamid']) && $user['steamid']) {
            $this->g_steamID = $user['steamid'];
        } else {
            return false;
        }
        
        if (isset($user['country']) && $user['country'])
            $this->country = $user['country'];

        if (isset($user['language']) && $user['language'])
            $this->setLanguage($user['language']);

    }
    /**
     * 设置为steam的语言
     * @param $language
     */
    protected function setLanguage($language){
        switch ($language){
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
    public function getInventory($game_id = 730, $language = ''){//730->csgo  570->dota
        $language = isset($language) && $language != '' ? $language : $this->language;
        //$url2 = 'http://steamcommunity.com/inventory/76561198003709290/730/2?l=schinese&count=75';
        return $url = 'http://steamcommunity.com/inventory/' .
            $this->g_steamID . '/' . $game_id . '/2?l='. $language .'&count=75';
    }

    /**
     * 获取市场所有商品
     *
     * @param int $currency
     * @param string $language
     * @param int $count
     * @return string
     */
    public function getMarket($currency = 0, $count = 20, $language = ''){
        $currency = isset($currency) && $currency != '' ? $currency : $this->currency;
        $language = isset($language) && $language != '' ? $language : $this->language;
        //$url = 'http://steamcommunity.com/market/popular?country=CN&language=schinese&currency=23&count=20';
        return $url = 'http://steamcommunity.com/market/popular?country=' . $this->country
            .'&language=' . $language
            . '&currency=' . $currency
            . '&count=' . $count;

    }

    /**
     * 获取交易url
     * @return string
     */
    public function getTradeUrl(){
        return $url = 'https://steamcommunity.com/profiles/'
            . $this->g_steamID . '/tradeoffers/privacy#trade_offer_access_url';
    }



























}