<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/23 14:52
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Api;


use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Request\Api\AuthRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\Api\AuthMiddleware;

/**
 * @AutoController()
 * @Middleware(AuthMiddleware::class)
 */
class AuthController extends AbstractController
{
    /**
     * @Inject()
     * @var AuthRequest
     */
    private $authValidator;

    /**
     * @Inject()
     * @var UserService
     */
    private $userService;

    /**
     * 注册
     * Author: Jason<dcq@kuryun.cn>
     */
    public function registerPost() {
        $res = $this->authValidator->doValidate('register');
        if($res['code'] !== 1) {
            return $this->response->fail(ErrorCode::BAD_PARAM, $res['msg']);
        }
        $params = $res['data'];
        $user = $this->userService->register($params);

        return $this->response->success(['user_info' => $user->getUser()]);
    }
}