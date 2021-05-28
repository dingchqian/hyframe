<?php
/**
 * Created by PhpStorm.
 * Script Name: ${FILE_NAME}
 * Create: 2021/3/13 下午11:04
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Service;


use HyperfX\Utils\Service;
use App\Kernel\Ky\Sms\Client;

class SmsService extends Service
{
    private $sign = '【悦邻里】';
    /**
     * 访客码发给好友
     * @param array $params
     * @return bool|mixed|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function visitorSms(array $params){
        $config = config('system.sms');
        try {
            $config['sms_account'] = '548391e7a20546e4b831c3b9a94ebaa1'; //长短信更换app code
            $sms = new Client($config['sms_account'], $config['sms_pwd'], $config['sms_type']);
            $code = mt_rand(100000, 999999);
            $content = [
                'param' => "**name**%3A{$params['name']}%2C**url**%3A{$params['url']}",
                'templateId' => '4eb9a16da54e464cb3c8f5757b00c06e',
                'api_long' => true
            ];

            if($sms->send($params['mobile'], $content) !== true){
                return $sms->getError();
            }

            redis()->setex(env('DB_PREFIX') . $params['mobile'], 300, $code);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 通知维修工验证码
     * @param array $params
     * @return bool|mixed|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function repairStaffSms(array $params){
        $config = config('system.sms');
        try {
            $sms = new Client($config['sms_account'], $config['sms_pwd'], $config['sms_type']);
            $code = mt_rand(100000, 999999);
            $content = [
                'param' => "**order**%3A{$params['order_no']}",
                'templateId' => '798541df3b6b4c7ba7ddc4de828666bd'
            ];
            //return $content;
            if($sms->send($params['mobile'], $content) !== true){
                return $sms->getError();
            }

            redis()->setex(env('DB_PREFIX') . $params['mobile'], 300, $code);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 登录验证码
     * @param array $params
     * @return bool|mixed|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function loginSms(array $params){
        $config = config('system.sms');
        try {
            $sms = new Client($config['sms_account'], $config['sms_pwd'], $config['sms_type']);
            $code = mt_rand(100000, 999999);
            $content = [
                'code' => $code
            ];
            if($sms->send($params['mobile'], $content) !== true){
                return $sms->getError();
            }
            redis()->setex(env('DB_PREFIX') . $params['mobile'], 300, $code);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 修改绑定手机
     * @param array $params
     * @return bool|mixed|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function changeMobile(array $params)
    {
        $config = config('system.sms');
        try {
            $sms = new Client($config['sms_account'], $config['sms_pwd'], $config['sms_type']);
            $code = mt_rand(100000, 999999);
            $content = [
                'code' => $code
            ];
            if($sms->send($params['mobile'], $content) !== true){
                return $sms->getError();
            }
            redis()->setex(env('DB_PREFIX') . $params['mobile'], 300, $code);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 业主认证
     * @param array $params
     * @return bool|mixed|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function bindHouse(array $params)
    {
        $config = config('system.sms');
        try {
            $sms = new Client($config['sms_account'], $config['sms_pwd'], $config['sms_type']);
            $code = mt_rand(100000, 999999);
            $content = [
                'code' => $code
            ];
            if($sms->send($params['mobile'], $content) !== true){
                return $sms->getError();
            }
            redis()->setex(env('DB_PREFIX') . $params['mobile'], 300, $code);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}