<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/24 14:45
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class ApiException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, Throwable $previous = null) {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}