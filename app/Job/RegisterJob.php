<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/25 14:43
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Job;

use App\Service\UserService;
use Hyperf\AsyncQueue\Job;

class RegisterJob extends Job
{
    public $params;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     * @var int
     */
    protected $maxAttempts = 2;

    public function __construct($params) {
        $this->params = $params;
    }

    public function handle() {
        //异步注册测试
        di(UserService::class)->register($this->params);
    }
}