<?php
$path = 'conf/cfg.csv';

$cfg = [];
if (($handle = fopen($path, 'r')) !== false) {
    while(($data = fgetcsv($handle, ',')) !== false) {
        $cfg[$data[0]] = $data[1];
    }
}
fclose($handle);