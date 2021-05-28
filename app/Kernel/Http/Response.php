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
namespace App\Kernel\Http;

use App\Constants\ErrorCode;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    public function success($data = [], $message = '', $url = ''): PsrResponseInterface
    {
        return $this->response->json([
            'code' => ErrorCode::SUCCESS_CODE,
            'msg' => $message,
            'data' => $data,
            'url' => $url
        ]);
    }

    public function fail($code, $message = '', $url = ''): PsrResponseInterface
    {
        return $this->response->json([
            'code' => $code,
            'msg' => $message,
            'url' => $url
        ]);
    }

    public function redirect($url, $status = 302): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Location', (string) $url)
            ->withStatus($status);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Server', 'Hyperf')
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }

    public function paramsError($message = '请求参数错误'): PsrResponseInterface
    {
        return $this->response->json([
            'code' => ErrorCode::INVALID_PARAM,
            'msg' => $message,
        ]);
    }

    public function error(string $msg, $data = [], $url = ''): PsrResponseInterface
    {
        return $this->response->json([
            'code' => ErrorCode::BAD_PARAM,
            'msg' => $msg,
            'data' => $data,
            'url' => $url
        ]);
    }

    public function json($data = []): PsrResponseInterface {
        return $this->response->json($data);
    }
}
