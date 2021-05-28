<?php
/**
 * Created by PhpStorm.
 * Script Name: Tools.php
 * Create: 2017/8/23 下午3:24
 * Description: 工具
 * Author: Doogie<461960962@qq.com>
 */
namespace App\Kernel\Ky\Sms\Qcloud;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

class Tools
{
    function getRandom() {
        return rand(100000, 999999);
    }
    function calculateSig($appkey, $random, $curTime, $phoneNumbers) {
        $phoneNumbersString = $phoneNumbers[0];
        for ($i = 1; $i < count($phoneNumbers); $i++) {
            $phoneNumbersString .= ("," . $phoneNumbers[$i]);
        }
        return hash("sha256", "appkey=".$appkey."&random=".$random
            ."&time=".$curTime."&mobile=".$phoneNumbersString);
    }
    function calculateSigForTemplAndPhoneNumbers($appkey, $random, $curTime, $phoneNumbers) {
        $phoneNumbersString = $phoneNumbers[0];
        for ($i = 1; $i < count($phoneNumbers); $i++) {
            $phoneNumbersString .= ("," . $phoneNumbers[$i]);
        }
        return hash("sha256", "appkey=".$appkey."&random=".$random
            ."&time=".$curTime."&mobile=".$phoneNumbersString);
    }
    function phoneNumbersToArray($nationCode, $phoneNumbers) {
        $i = 0;
        $tel = array();
        do {
            $telElement = new \stdClass();
            $telElement->nationcode = $nationCode;
            $telElement->mobile = $phoneNumbers[$i];
            array_push($tel, $telElement);
        } while (++$i < count($phoneNumbers));
        return $tel;
    }
    function calculateSigForTempl($appkey, $random, $curTime, $phoneNumber) {
        $phoneNumbers = array($phoneNumber);
        return $this->calculateSigForTemplAndPhoneNumbers($appkey, $random, $curTime, $phoneNumbers);
    }

    function sendCurlPost($url, $dataObj) {
        $client = new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            /*'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],*/
        ]);

        $response = $client->post($url, [
            'json' => $dataObj
        ]);
        return  $response->getBody()->getContents();
    }
}
