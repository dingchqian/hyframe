<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/24 11:39
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Api;


use App\Controller\AbstractController;
use App\Request\Api\UserRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\Api\AuthMiddleware;

/**
 * @AutoController()
 * @Middleware(AuthMiddleware::class)
 */
class UserController extends AbstractController
{
    /**
     * @Inject()
     * @var UserService
     */
    private $userService;

    /**
     * @Inject()
     * @var UserRequest
     */
    private $userRequest;

    /**
     * 完善用户信息
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setUserPost() {
        $params = $this->userRequest->doValidate('setUser');
        $user = $this->userService->setUser($params);

        return $this->response->success(['user_info' => $user->getUser()], '保存成功');
    }
}