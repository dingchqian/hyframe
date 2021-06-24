<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest;

use App\Constants\ErrorCode;
use App\Service\Dao\SettingDao;
use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;
use Hyperf\Utils\Codec\Json;

/**
 * Class HttpTestCase.
 * @method get($uri, $data = [], $headers = [])
 * @method post($uri, $data = [], $headers = [])
 * @method json($uri, $data = [], $headers = [])
 * @method file($uri, $data = [], $headers = [])
 * @method request($method, $path, $options = [])
 */
abstract class HttpTestCase extends TestCase
{
    /**
     * @var string
     */
    public static $token;
    protected $codeArr = [ErrorCode::SUCCESS_CODE, ErrorCode::TOKEN_INVALID, ErrorCode::BAD_PARAM,
        ErrorCode::INVALID_PARAM, ErrorCode::ERROR_PARAM];

    /**
     * @var Client
     */
    protected $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class, ['baseUri' => 'http://127.0.0.1:9901']);
        di(SettingDao::class)->settings();
    }

    public function __call($name, $arguments)
    {
        return $this->client->{$name}(...$arguments);
    }

    public function getToken(string $token = '')
    {
        if($token){
            redis()->set('test_token', $token);
        }
        return redis()->get('test_token1');
    }

    /**
     * 生成签名
     * Author: fudaoji<fdj@kuryun.cn>
     * @param array $params
     * @return string
     */
    protected function getSign(array $params):string
    {
        if(count($params) == 0){
            $params_str = '';
        }else{
            //签名步骤一：按字典序排序参数
            ksort($params);
            $params_str = "";
            foreach ($params as $k => $v)
            {
                if($k != "sign"){
                    $params_str .= ($k . "=" . Json::encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "&");
                }
            }
            $params_str = trim($params_str, "&");
        }
        //签名步骤二：在string后加入KEY
        $params_str .= env('APP_KEY');
        //签名步骤三：MD5加密
        return md5($params_str);
    }

    /**
     * 进一步封装请求
     * @param string $url
     * @param array $params
     * @param bool $needToken
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    protected function doJson(string $url, array $params, bool $needToken = true)
    {
        $headers = [
            'sign' => $this->getSign($params)
        ];
        $needToken && $headers['token'] = $this->getToken();
        return $this->json($url, $params, $headers);
    }
}