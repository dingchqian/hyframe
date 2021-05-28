<?php

declare(strict_types=1);

namespace App\Middleware;


use App\Controller\Admin\BaseController;
use App\Service\Dao\AdminDao;
use App\Service\Dao\SettingDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminMiddleware extends BaseController implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(!$aid = $this->session->get('aid')) {
            return $this->response->redirect('/admin/auth/login');
        }

        [$module, $controller, $action] = explode('/', substr($this->request->getRequestUri(), 1));
        Context::set('module', $module);
        Context::set('controller', $controller);
        Context::set('action', $action);

        $adminInfo = $this->adminDao->getOne($aid);
        Context::set('adminInfo', $adminInfo);
        //系统自定义配置
        $this->container->get(SettingDao::class)->settings();
        return $handler->handle($request);
    }
}