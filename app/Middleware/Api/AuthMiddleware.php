<?php

declare(strict_types=1);

namespace App\Middleware\Api;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Service\UserAuth;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractController implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $module = '';
    protected $controller = '';
    protected $action = '';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$this->module, $this->controller, $this->action] = explode('/', substr($request->getUri()->getPath(), 1));
        if(($res = $this->checkToken()) !== true){
            return $res;
        }

        return $handler->handle($request);
    }

    /**
     * 校验登录
     * Author: Jason<dcq@kuryun.cn>
     */
    protected function checkToken(){
        $this->checkSign();
        $token = $this->request->getHeaderLine(UserAuth::X_TOKEN);
        if($token){
            UserAuth::instance()->reload($token);
        }

        if($this->needToken() && !UserAuth::instance()->build()->getUserId()){
            return $this->response->fail(ErrorCode::TOKEN_INVALID, '登录会话过期');
        }
        return true;
    }

    /**
     * 签名验证
     * Author: fudaoji<fdj@kuryun.cn>
     */
    protected function checkSign(){
        $params = $this->request->all();
        if(count($params) == 0){
            $params_str = '';
        }else{
            //签名步骤一：按字典序排序参数
            ksort($params);
            $params_str = "";
            foreach ($params as $k => $v)
            {
                if($k != "sign"){
                    $params_str .= ($k . "=" . json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "&");
                }
            }
            $params_str = trim($params_str, "&");
        }
        //签名步骤二：在string后加入KEY
        $params_str .= env('APP_KEY');
        //签名步骤三：MD5加密
        $sign = md5($params_str);
        //判断sign
        if($sign !== $this->request->getHeaderLine('sign')){
            return $this->response->fail(ErrorCode::ERROR_PARAM, '签名错误');
        }
    }

    /**
     * 是否需要token
     * @return bool
     * Author: fudaoji<fdj@kuryun.cn>
     */
    protected function needToken(){
        if(in_array(strtolower($this->controller), ['home'])){
            return false;
        }
        return true;
    }
}