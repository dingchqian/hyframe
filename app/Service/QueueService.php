<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/25 14:43
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service;

use App\Job\RegisterJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory) {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 生产消息.
     * @param $params
     * @param int $delay 延时时间 单位秒
     * @return mixed
     */
    public function push($params, int $delay = 0): bool {
        return $this->driver->push(new RegisterJob($params), $delay);
    }
}