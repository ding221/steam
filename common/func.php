<?php

/**
 * curl访问（get方式）
 *
 * @param string    $url
 * @param int       $timeout
 * @method       get
 * @return mixed
 */
function curlGet($url, $timeout = 60) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//CURL请求https
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'Errno ' . curl_error($curl);
    }
    curl_close($curl);
    return $result;
}

/**
 * curl访问（post 方式）
 *
 * @params  url     url   请求网址
 * @params  data    mixed 请求参数
 * @params  time    int   过期时间
 * return  mixed
 */
function httpsPost($url = '', $data = [], $time = 60){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, $time);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Errno ' . curl_error($ch);
    }
    curl_close($ch);
    return $result;
}

function println($str) {
    print("$str\n");
}

function charCodeAt($str, $index) {
    $char = mb_substr($str, $index, 1, 'UTF-8');
    if (mb_check_encoding($char, 'UTF-8')) {
        $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
        return hexdec(bin2hex($ret));
    } else {
        return null;
    }
}

/**
 * 将ANCSII码转换为字符串
 *
 * @param $codes
 * @return string
 */
function fromCharCode($codes) {
    if (is_scalar($codes)) $codes= func_get_args();
    $str= '';
    foreach ($codes as $code) $str.= chr($code);
    return $str;
}
//fromCharCode(78,79,60);
