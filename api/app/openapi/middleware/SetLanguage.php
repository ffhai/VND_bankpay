<?php
declare(strict_types=1);
namespace app\openapi\middleware;

use app\common\model\ConfigModel;
use think\facade\Lang;

class SetLanguage
{
    public function handle($request, \Closure $next)
    {
        $locale = (new ConfigModel())->getContentByKey('openapi_language');
        $locale = !empty($locale)? $locale : 'zh-cn';
        Lang::setLangSet($locale);
        Lang::load(dirname(__DIR__) . '/lang/' . $locale . '.php', $locale);
        return $next($request);
    }
}
