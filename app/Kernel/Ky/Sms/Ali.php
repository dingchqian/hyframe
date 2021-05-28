<?php
/**
 * Script name: Ali.php
 * Created by PhpStorm.
 * Create: 2016/7/14 14:37
 * Description: 阿里短信
 * Author: Doogie<461960962@qq.com>
 */

namespace App\Kernel\Ky\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

class Ali
{
    public $timeout = 30; //超时
    private $apiUrl;	//发送地址
    private $apiUrlLong;
    private $appCode;	//用户名
    private $error;
    private $signId = '55419de6df4c42ef8f081e2e241580e3';
    private $templateId = '908e94ccf08b4476ba6c876d13f084ad';

    function __construct($app_code, $password = '') {
        $this->apiUrl 	= 'https://gyytz.market.alicloudapi.com';
        $this->apiUrlLong 	= 'https://gyytz2.market.alicloudapi.com';
        $this->appCode = $app_code;
    }

    /**
     * 发送短信接口
     * @param string $mobile
     * @param array|string $content
     * @return bool
     * author: Doogie<461960962@qq.com>
     */
    public function send($mobile='', $content){
        $extra['headers'] = [
            'Authorization' => 'APPCODE ' . $this->appCode
        ];
        $sign_id = empty($content['signId']) ? $this->signId : $content['signId'];
        $template_id = empty($content['templateId']) ? $this->templateId : $content['templateId'];
        $code = empty($content['code']) ? $content : $content['code'];
        $param = empty($content['param']) ? "**code**%3A{$code}%2C**minute**%3A5" : $content['param'];
        $querys = "mobile={$mobile}&smsSignId={$sign_id}&templateId={$template_id}&param={$param}";
        if(empty($content['api_long'])){
            $api_url = $this->apiUrl;
            $path = "/sms/smsSend". "?" . $querys;
        }else{
            $api_url = $this->apiUrlLong;
            $path = "/sms/smsSendLong". "?" . $querys;
        }

        $client = new Client([
            'base_uri' => $api_url,
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => $this->timeout,
            /*'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2,
            ],*/
        ]);

        $response = $client->post($path, $extra);
        $res = json_decode($response->getBody()->getContents(), true);
        if((int)$res['code'] !== 0){
            $this->setError($res['msg']);
            return false;
        }
        return true;
    }

    private function setError($msg = ''){
        $this->error = !empty($msg) ? $msg : '未知错误';
    }

    /**
     * 返回错误信息
     * @return mixed
     * author: Doogie<461960962@qq.com>
     */
    public function getError(){
        return $this->error;
    }

}
