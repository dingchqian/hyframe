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
namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("请求异常")
     */
    const  CATCH_EXCEPTION = 0;

    /**
     * @Messsage("请求成功, 结果不为空")
     */
    const SUCCESS_CODE = 1;

    /**
     * @Message("请求成功, 结果为空")
     */
    const EMPTY_RESULT = 2;

    /**
     * @Message("请求失败")
     */
    const FAILED_CODE = 2000;

    /**
     * @Message("参数格式错误")
     */
    const ERROR_PARAM  = 2001;

    /**
     * @Message("参数值非法")
     */
    const INVALID_PARAM = 2002;

    /**
     * @Message("参数值非法, 服务端强制客户端提示")
     */
    const BAD_PARAM = 2003;

    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("Token 已失效")
     */
    const TOKEN_INVALID = 911;

    /**
     * @Message("参数非法")
     */
    const PARAMS_INVALID = 2000;
}
