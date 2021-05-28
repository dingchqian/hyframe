<?php
/**
 * Created by PhpStorm.
 * Script Name: Yb.php
 * Create: 2018/7/4 19:37
 * Description: 快递100
 * Author: Doogie<fdj@kuryun.cn>
 */

namespace App\Kernel\Ky\Express;

use App\Kernel\Ky\Snoopy;

class Yb
{
    private $error = '';
    private $config = [
        'app_code' => '42e392bfa63d44e9b3325d77c96d652b',
    ];

    public function __construct($config = [])
    {
        $config && $this->config = array_merge($this->config, $config);
    }

    public function query($params = []){
        //$url ='http://api.kuaidi100.com/api?id='.$this->config['app_code'].'&com='.$com.'&nu='.$nu.'&show=0&muti=1&order=desc';
        $com = $params['com'];
        $nu = $params['nu'];
        $url = 'https://m.kuaidi100.com/query?type='.$com.'&postid='.$nu.'&id=1&valicode=&temp=';
        $post_data = [
            'type' => $com, 'postid' => $nu, 'id' => 1, 'valicode' => '', 'temp' => '',
            'platform' => 'MWWW', 'coname' => $com
        ];
        $res = $this->sendPost($url, $post_data);
        if($res){
            $res = json_decode($res, true);
            if($res && isset($res['status'])){
                if($res['status'] == 200){
                    //0在途，1揽收，2疑难，3签收，4退签，5派件，6退回，7转单，10待清关，11清关中，12已清关，13清关异常，14收件人拒签等13个状态
                    $data = [];
                    if(count($res['data'])){
                        foreach ($res['data'] as $r){
                            $data[] = ['time' => $r['time'], 'context' => $r['context']];
                        }
                    }
                    return [
                        'success' => true,
                        'com' => $res['com'],
                        'num' => $res['nu'],
                        'data' => $data,
                        'status' => $res['state']
                    ];
                }
            }
        }else{
            $this->setError(-1);
        }
        return false;
    }

    public function sendPost($url = '', $post_data = []){
        //优先使用curl模式发送数据
        if (0 && function_exists('curl_init') == 1){
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $url);
            curl_setopt ($curl, CURLOPT_HEADER,0);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
            curl_setopt ($curl, CURLOPT_TIMEOUT,5);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

            $res = curl_exec($curl);
            curl_close ($curl);
        }else{
            $snoopy = new Snoopy();
            $snoopy->referer = 'http://www.google.com/';//伪装来源
            $snoopy->fetch($url);
            $res = $snoopy->results;
        }
        return $res;
    }

    /**
     * 设置错误
     * @param int $code
     * @return mixed
     * Author: Doogie<fdj@kuryun.cn>
     */
    private function setError($code = -1){
        $errors = [
            -1 => '未知错误',
            201 => '快递单号错误',
            203=> '快递公司不存在',
            204 => '快递公司识别失败',
            205 => '没有信息',
            207 => '该单号被限制，错误单号'
        ];
        $this->error = isset($errors[$code]) ? $errors[$code] : $errors[0];
    }

    public function getError(){
        return $this->error;
    }
}