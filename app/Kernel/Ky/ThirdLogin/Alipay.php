<?php
/**
 * Created by PhpStorm.
 * Script Name: AlipayLogin.php
 * Create: 2021/3/28 下午11:05
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Kernel\Ky\ThirdLogin;


// 使用 Guzzle 做请求操作
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

// 支付宝APP 第三方登录
// 特点：相比微信，支付宝所有敏感信息都在服务端完成， 保证了安全
//
// 流程：
// 1.服务端到APP infoStr
// 2.APP端 通过infoStr 获得 auth_code
// 3.服务端通过 auth_code 拿到请求 token
// 4.服务端通过 token 获得用户信息

class Alipay
{

    protected $appId = '2021002132615082';
    protected $pid = '9361640917';
    protected $privateKey = 'MIIEowIBAAKCAQEAl5GHxxY/D1Ze8NAi7KaFb4er0+zVtVWUlxpKciJ7cPjB0DNieoSM5xwTDHTLUe5veVo5OAAHs+RVQYq0B6cfVzzZUZJbuIaxs2HUQqPVqWtM9+pvC1z8MbrcRNFeyXBd+olPvG8+U03KTk3IGrOxibk5cKtujWafSSlFGzx+JSvoYJRRs1vIuq1NhznxS7LfvF8gpKVQF9e/kj4IguwPgtNLl7GPHb86K3WJsdYkBjYdZahSJhGNj91IVxOE2TYgWT0vJyyYObg+ZjHILaYEXs1gHF6q+YK+hoTWDMUxV1/3Sp5m0nCtIpTEac5/FLDJ+ifj0OeglXwaOlVeNB2MRwIDAQABAoIBABmRK/UyVHrS1/sJMQr4YAPbP+nkxnvrLsB6Z9pu5KG+5HqpYFBIcbg+bRwpy25LqTQwOvyuTJ5Uwujma83qrAe8NPBknuI2nl0jAHopRs5oUjhzrD2fds8wtR1KsBGMyd2MMg4YOcw1kA27YyjV7PnNa9oMoP5rNC1UL9sTTjgYt4Q6Kr8OVgskzK8jQnNMM+PIE6Ehq4J0wxbRvNDZFMeKmNQmbVJNtiirvRtRwCpQJ2UbTY32HvploPxOyRQFuMouDZ2aogig1gqBixLmaHQ6jSqaXVgx0bUSlGdgJhq+Wepjsy3UYu1KMgZWtjpRAIal/O/01FScMb/u/wdzXTkCgYEA6rydcJSRxSLMiUDyTPkNrEfGH4bB1P1tCcmyyd3GUmpiKwENwFZvsWqMfx+l0t5J4IHZd71eV8VFoDuoF/3NH9fDWZLGC57xIUjwehCNWW737oHeGeSiBn2rCES77FKSEQow7oyHrHUv1i3X/2/J0v61LsIwsiLzr5Pl5KkVNxUCgYEApUxMrSyQynol6tURHegzbTWQtGSaXAv72F3LX+5lLJTsxtxzvwPRql5bU3HRkhwVDBGqb2UGjaj0WFU5g1jPRHNUpiOY7Imq9mk+HoQQamgoOH6zmxh2nbUnq2ri3UFHmNgRdEEyOwRkXhfSLOdbeJOZtpBgLctrk++eBWaTDOsCgYAW2uiFZqHOzPWXQ5CT+Afhx1c+CJPk1gwA0PesegBuU9ddEowxNvlHD/XABufRYT1WM65l3zVJXHbMBCL4uwh22j42AXlw9jfKItzvNZ9dntVbDp/+a2lvMlstwet+Ngfsys4628n1+679rpcCUvMWrSAc/mFZZtfNN5xBqEjdhQKBgD9VKpO3XYqWnmyJUlOZIgsX2OelHBdkaAwQc9m/p3gbX1UaJibruauDF46zL07B/7ZuFlUz6fzg3S3zCWQv5MofPjGhtff7D0v2KtzaUMfUPITY1sv35YqrXBWrkFyhpGMFdjqKuEowdpwumFKoGj3qn5x5WMBzDjbSOkNrd7AdAoGBALUBbLoc8u1mTz15ljrG9RYEOmqfMjqgGWeikMQ8V1oDgFS8cie8Hhi/X955+vjnlQLCzTVm+uJwjkXu13GRCt88447z3fcnvamo+Wn1vvSrpMUYeo1WWZnP/TdLLTuU8j6DjFymi0K33gfgHoTd9+cz6PIyX7U3dVb6HmbGml/A';

    public function __construct($options = [])
    {
        $this->appId = $options['appid'];
        $this->pid = $options['pid'];
        $this->privateKey = $options['private_key'];
    }

    /**
     * InfoStr APP登录需要的的infostr
     *
     * @return String
     */
    public function infoStr()
    {
        $infoStr = http_build_query([
            'apiname' => 'com.alipay.account.auth',
            'method' => 'alipay.open.auth.sdk.code.get',
            'app_id' => $this->appId,
            'app_name' => 'sc',
            'biz_type' => 'openservice',
            'pid' => $this->pid,
            'product_id' => 'APP_FAST_LOGIN',
            'scope' => 'kuaijie',
            'target_id' => date('YmdHis').rand(1000, 9999), //商户标识该次用户授权请求的ID，该值在商户端应保持唯一
            'auth_type' => 'AUTHACCOUNT', // AUTHACCOUNT代表授权；LOGIN代表登录
            'sign_type' => 'RSA2',
        ]);
        $infoStr .= '&sign=' . urlencode($this->enRSA2($infoStr));
        return $infoStr;
    }

    /**
     * AlipayToken 获得用户 请求token, 通过它获得 用户信息
     *
     * 需要按照支付宝加签流程来。
     */
    public function userInfo($app_auth_token)
    {
        $infoArr = [
            'method' => 'alipay.system.oauth.token',
            'app_id' => $this->appId,
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'code' => $app_auth_token,
            'grant_type' => 'authorization_code',
        ];

        $signStr = $this->myHttpBuildQuery($infoArr);
        $sign = urlencode($this->enRSA2($signStr));
        $qureStr = $signStr . '&sign=' . $sign;

        $res = new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 10
        ]);
        $body = $res->get('https://openapi.alipay.com/gateway.do?' . $qureStr)->getBody()->getContents();
        $body = json_decode($body);
        if (!isset($body->alipay_system_oauth_token_response->access_token)) {
            return '接口异常';
        } else {
            $autho_token = $body->alipay_system_oauth_token_response->access_token;
            $userinfo = $this->aliPayUserInfo($autho_token);
            return json_decode(json_encode($userinfo), true); // 或则 返回 json_encode($userinfo) 根据实际需求来
        }
    }

    /**
     * AliPayUserInfo 通过 token 获取用户信息
     */
    private function aliPayUserInfo($autho_token)
    {
        $infoArr = [
            'method' => 'alipay.user.info.share',
            'app_id' => $this->appId,
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'auth_token' => $autho_token,
        ];

        $signStr = $this->myHttpBuildQuery($infoArr);
        $sign = urlencode($this->enRSA2($signStr));
        $qureStr = $signStr . '&sign=' . $sign;

        $res = new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 10
        ]);
        $body = $res->get('https://openapi.alipay.com/gateway.do?' . $qureStr)->getBody()->getContents();
        $body = json_decode($body);
        if (!isset($body->alipay_user_info_share_response)) {
            return '接口异常';
        }
        $body = $body->alipay_user_info_share_response;
        return $body;
    }

    /**
     * enRSA2 RSA加密
     *
     * @param String $data
     * @return String
     */
    private function enRSA2Bak($data)
    {
        $str = chunk_split(trim($this->privateKey), 64, "\n");
        $key = "-----BEGIN RSA PRIVATE KEY-----\n$str-----END RSA PRIVATE KEY-----\n";
        // $key = file_get_contents(storage_path('rsa_private_key.pem')); 为文件时这样引入
        $signature = '';
        $signature = openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256) ? base64_encode($signature) : NULL;
        return $signature;
    }

    function enRSA2($data, $signType = 'RSA2')
    {
        $res = "-----BEGIN RSA PRIVATE KEY-----\n".
            wordwrap($this->privateKey, 64, "\n", true).
            "\n-----END RSA PRIVATE KEY-----";
        if ('RSA2' == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }
        return base64_encode($sign);
    }

    /**
     * myHttpBuildQuery 返回一个 http Get 传参数组
     * 之所以不用 自带函数 http_build_query 时间带 ‘:’ 会被转换
     *
     * @param Array
     * @return String
     */
    private function myHttpBuildQuery($dataArr)
    {
        ksort($dataArr);
        $signStr = '';
        foreach ($dataArr as $key => $val) {
            if (empty($signStr)) {
                $signStr = $key . '=' . $val;
            } else {
                $signStr .= '&' . $key . '=' . $val;
            }
        }
        return $signStr;
    }
}
