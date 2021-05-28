<?php

declare(strict_types=1);

namespace App\Middleware\Api;

use App\Constants\ErrorCode;
use App\Controller\Api\BaseController;
use App\Service\Dao\UserDao;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\Codec\Json;

class AuthMiddleware extends BaseController implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $module = '';
    protected $controller = '';
    protected $action = '';
    protected $token;
    protected $loginSession;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$this->module, $this->controller, $this->action] = explode('/', substr($this->request->getUri()->getPath(), 1));
        Context::set('module', $this->module);
        Context::set('controller', $this->controller);
        Context::set('action', $this->action);

        if(($res = $this->checkToken()) !== true){
            return $res;
        }

        return $handler->handle($request);
    }

    /**
     * 是否需要token
     * @return bool
     * Author: fudaoji<fdj@kuryun.cn>
     */
    protected function needToken(){
        if(in_array(strtolower($this->controller), ['home', 'sms', 'auth', 'mall', 'o2o', 'tbk', 'forum'])){
            return  false;
        }
        return  true;
    }

    /**
     * 校验登录
     * Author: fudaoji<fdj@kuryun.cn>
     */
    protected function checkToken(){
        $this->checkSign();
        $token = $this->request->getHeaderLine($this->tokenName);
        if($token){
            $this->token = $token;
            Context::set('token', $token);
            $login_session = \redis()->get($this->token);
            if($login_session){ //续时
                $this->loginSession = Json::decode($login_session, true);
                \redis()->setex($this->token, 86400 * 7, Json::encode($this->loginSession));
                $this->setUserInfo();
            }
        }

        if($this->needToken() && empty($this->loginSession)){
            return $this->response->fail(ErrorCode::TOKEN_INVALID, '登录会话过期');
        }
        return true;
    }
    protected function checkTokenBak(){
        $this->checkSign();
        $need_token = $this->needToken();
        $token = $this->request->getHeaderLine($this->tokenName);
        if($need_token && empty($token)){
            return $this->response->fail(ErrorCode::TOKEN_INVALID, '登录会话过期');
        }
        $this->token = $token;
        Context::set('token', $token);
        $login_session = \redis()->get($this->token);
        if($login_session){ //续时
            $this->loginSession = Json::decode($login_session, true);
            \redis()->setex($this->token, 86400 * 7, Json::encode($this->loginSession));
            $this->setUserInfo();
        }
        if($need_token && empty($this->loginSession)){
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
     * 用户信息
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    private function setUserInfo()
    {
        $user = $this->container->get(UserDao::class)->getOne($this->loginSession['uid']);
        Context::set("user_info", $user);
        return $user;
    }
}