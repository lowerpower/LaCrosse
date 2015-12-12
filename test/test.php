<?php
function getallheaders() {
    return array();
}

require_once('request.breq');

$packet = file_get_contents('test/packets/197-byte-weather.dump');

$nybs = post2nyb($packet);
$wuData = assembleWUData($nybs, 1);
echo "$wuData\n";
