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
use App\Service\UserAuth;
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
     * 获取用户详情
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getUserPost() {
        return $this->response->success(['user_info' => UserAuth::instance()->getUser()], '获取成功');
    }
}