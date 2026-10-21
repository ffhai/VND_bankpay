<?php
namespace app;

use app\common\enums\CodeEnum;
use app\common\exception\BaseException;
use app\common\exception\NotAuthException;
use app\common\exception\NotPermsException;
use app\common\utils\AjaxUtils;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 *
 * 所有异常统一转成 {code, msg, data} 信封，并按异常类型给出语义化 HTTP 状态码：
 * 未登录 401 / 无权限 403 / 参数与业务错误 422 / 系统异常 500；成功响应仍为 200。
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 已经构造好响应体的异常（如验证器 goCheck 抛出的错误信封）直接使用
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }

        // 业务异常：保留自身 code，供前端区分未登录 / 无权限 / 业务错误
        if ($e instanceof BaseException) {
            return AjaxUtils::error($e->getMessage(), $e->data, $e->getCode(), $this->httpStatus($e));
        }

        // 参数校验异常
        if ($e instanceof ValidateException) {
            return AjaxUtils::error($e->getMessage(), [], CodeEnum::ERROR, 422);
        }

        // 404 等 HTTP 异常沿用框架处理
        if ($e instanceof HttpException) {
            return parent::render($request, $e);
        }

        // 其余未预期异常：统一返回信封，避免把 HTML 错误页或内部细节抛给前端
        return AjaxUtils::error($this->safeMessage($e), [], CodeEnum::ERROR, 500);
    }

    /**
     * 业务异常对应的 HTTP 状态码
     */
    private function httpStatus(BaseException $e): int
    {
        if ($e instanceof NotAuthException) {
            return 401;
        }
        if ($e instanceof NotPermsException) {
            return 403;
        }

        return 422;
    }

    /**
     * 未预期异常的对外提示：调试模式保留原始信息便于排查，生产只给通用提示
     */
    private function safeMessage(Throwable $e): string
    {
        return app()->isDebug() ? $e->getMessage() : '系统繁忙，请稍后重试';
    }
}
