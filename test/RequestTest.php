<?php
require_once('base.php');

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function testCanBeNegated()
    {
        $packet = file_get_contents('test/packets/197-byte-weather.dump');

        $nybs = post2nyb($packet);
        $wuData = assembleWUData($nybs, 1);

        $expected = "&tempf=52.88&humidity=82&dewptf=48.86&indoortempf=68.36&indoorhumidity=59&windspeedmph=0.00&windgustmph=0.00&winddir=270&baromin=29.91&rainin=0.00&dailyrainin=0.00&softwaretype=phpLaxWeatherBro";
        $this->assertEquals($expected, $wuData);
    }
}


