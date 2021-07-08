<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/25 14:45
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Api;


use App\Amqp\Producer\DemoProducer;
use App\Controller\AbstractController;
use App\Service\QueueService;
use Hyperf\Amqp\Producer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Utils\ApplicationContext;

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
     * Author: Jason<dcq@kuryun.cn>
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

    /**
     * AMQP消息队列
     * Author: Jason<dcq@kuryun.cn>
     */
    public function amqpMsg() {
        $message = new DemoProducer([
            'mobile' => '13212345673',
            'password' => '123456',
            'username' => 'amqp消息队列注册测试'
        ]);
        $producer = ApplicationContext::getContainer()->get(Producer::class);
        $result = $producer->produce($message);

        return $this->response->success(['data' => $result], 'amqp消息队列注册投递成功');
    }
}