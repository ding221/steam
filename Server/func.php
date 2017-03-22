<?php
function get_params($data){
    //username=1&password=2&captcha=3
    $data = explode('&', $data);
    $arr = [];
    foreach ($data as $key => $item) {
        $value = explode('=', $item);
        $arr[$value[0]] = isset($arr[$value[1]]) ? $value[1] : '';
    }
    return $arr;
}
