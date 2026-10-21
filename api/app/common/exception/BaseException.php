<?php


namespace app\common\exception;

use Exception;

/**
 * 自定义异常基类
 */
abstract class BaseException extends Exception
{
    /**
     * 错误描述
     * @var string
     */
    public $message = '';

    /**
     * 错误编码
     * @var int
     */
    public $code    = 0;

    /**
     * 响应数据
     * @var array
     */
    public $data    = [];
}