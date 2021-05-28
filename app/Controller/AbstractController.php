<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Kernel\Http\Response;
use App\Service\Dao\SettingDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * 构造函数
     * Author: Jason<dcq@kuryun.cn>
     */
    public function __construct()
    {
        $this->response = $this->container->get(Response::class);
        $this->request = $this->container->get(RequestInterface::class);
        $this->logger = di(LoggerFactory::class)->get('log', 'default');
        if(! config('system')){
            di(SettingDao::class)->settings();
        }
    }
}
