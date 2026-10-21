<?php


namespace app\common\http\middleware;

use Closure;

/**
 * 自定义跨域中间件
 *
 * 配置了白名单（config/cors.php 的 allow_origins）时只回显命中的 Origin；
 * 未配置白名单时回显请求的 Origin 便于本地联调，且不下发 Allow-Credentials，
 * 避免出现「* + Allow-Credentials」这种浏览器会拒绝、语义上又等同于放开任意站点的组合。
 */
class AllowCrossDomain
{
    /**
     * 跨域处理
     *
     * @author zero
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowOrigin = $this->resolveAllowOrigin((string) $request->header('origin', ''));

        if ($allowOrigin !== '') {
            header('Access-Control-Allow-Origin: ' . $allowOrigin);
            header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
            header('Access-Control-Allow-Headers: ' . $this->allowHeaders($request));
            header('Access-Control-Max-Age: 1728000');
        }
        // 响应内容随 Origin 变化，避免被中间层按同一个 URL 缓存串用
        header('Vary: Origin');

        if (strtoupper($request->method()) == 'OPTIONS') {
            return response();
        }
        return $next($request);
    }

    /**
     * 计算允许回显的 Origin，空字符串表示不允许跨域
     */
    private function resolveAllowOrigin(string $origin): string
    {
        if ($origin === '') {
            return '';
        }

        $allowOrigins = array_filter(
            array_map('trim', explode(',', (string) config('cors.allow_origins', '')))
        );
        if (empty($allowOrigins)) {
            return $origin;
        }

        return in_array($origin, $allowOrigins, true) ? $origin : '';
    }

    /**
     * 允许的请求头：优先回显预检声明，未声明时给出默认集合
     */
    private function allowHeaders($request): string
    {
        $requested = (string) $request->header('access-control-request-headers', '');

        return $requested !== '' ? $requested : 'Api-Token,Content-Type,X-Requested-With';
    }
}