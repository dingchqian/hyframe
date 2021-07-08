<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/25 14:45
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Api;


use App\Controller\AbstractController;
use App\Service\QueueService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class QueueController extends AbstractController
{
    /**
     * @Inject()
     * @var QueueService
     */
    protected $service;

    /**
     * 传统模式投递消息
     */
    public function index()
    {
        $data = $this->service->push([
            'mobile' => '13212345671',
            'password' => '123456',
            'username' => '异步注册测试'
        ], 10);

        return $this->response->success(['data' => $data], '异步注册消息投递成功');
    }
}