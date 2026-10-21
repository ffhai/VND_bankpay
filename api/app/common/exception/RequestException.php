<?php


namespace app\common\exception;

use app\common\enums\CodeEnum;
use Throwable;

class RequestException extends BaseException
{
    public function __construct($message = '', $code = 0, $data = [], Throwable $previous = null)
    {
        $this->data     = $data;
        $this->code     = $code    ?: CodeEnum::ERROR;;
        $this->message  = $message ?: CodeEnum::getMsgByCode($this->code);;
        parent::__construct($this->message, $this->code, $previous);
    }
}