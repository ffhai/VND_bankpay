<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = new think\App(dirname(__DIR__));
$app->initialize();
$app->debug(true);
use think\facade\Db;
use think\facade\View;
use think\facade\Lang;
use app\openapi\middleware\SetLanguage;
use app\common\utils\AjaxUtils;
use app\openapi\validate\OrderValidate;
function langCheck($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo 'PASS ' . $message . PHP_EOL;
}
$config = config('database');
$config['connections']['mysql']['prefix'] = 'lang_test_';
config($config, 'database');
Db::execute('CREATE TEMPORARY TABLE lang_test_config LIKE dt_config');
Db::name('config')->insert(['key'=>'openapi_language','content'=>'zh-cn','group_id'=>1]);
$app->http->name('openapi');
$setLanguage = function () use ($app) {
    (new SetLanguage())->handle($app->request, function ($request) { return null; });
};
$setLanguage();
langCheck(Lang::getLangSet() === 'zh-cn', 'default Chinese');
foreach (['zh-cn', 'en', 'vi'] as $locale) {
    Db::name('config')->where('key','openapi_language')->update(['content'=>$locale]);
    $setLanguage();
    $messages = Lang::get();
    langCheck(Lang::getLangSet() === $locale, 'backend language ' . $locale);
    langCheck(count(array_filter($messages, function ($message) { return is_string($message) && $message !== ''; })) === count($messages), 'native language pack complete ' . $locale);
    langCheck(lang('无权限[{:url}]', ['url'=>'openapi/order/query']) !== '无权限[{:url}]', 'native variable interpolation ' . $locale);
    langCheck(AjaxUtils::error('订单不存在')->getData()['msg'] === $messages['订单不存在'], 'API error ' . $locale);
    langCheck(AjaxUtils::success([])->getData()['msg'] === $messages['获取成功'], 'API success ' . $locale);
    $response = (new app\ExceptionHandle($app))->render($app->request, new app\common\exception\RequestException('参数错误'));
    langCheck($response->getData()['msg'] === $messages['参数错误'], 'business exception ' . $locale);
    $validator = new OrderValidate();
    langCheck(!$validator->scene('createOrder')->check(['trade_no'=>'test','amount'=>1,'notify_url'=>'https://example.test']), 'missing pay_type rejected');
    langCheck($validator->getError() === $messages['pay_type_required'], 'localized required validation ' . $locale);
    $validator = new OrderValidate();
    langCheck(!$validator->scene('createOrder')->check(['pay_type'=>'bank2bank','trade_no'=>'test','amount'=>'1.5','notify_url'=>'https://example.test']) && $validator->getError() === $messages['amount_invalid'], 'localized amount validation ' . $locale);
    $pay = new app\openapi\controller\Pay($app);
    $html = View::fetch(dirname(__DIR__).'/app/openapi/view/pay/index.html', [
        'qrcode'=>'data:image/png;base64,','bankinfo'=>['short_name'=>'Test Bank'],
        'bankuser'=>['name'=>'Test User'],'order'=>['amount'=>123,'bankcard'=>['bank_number'=>'123456789'],'ps'=>'ABC123','status'=>1], 'timeout'=>120,
    ]);
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    langCheck(strpos($html, '<html lang="'.$locale.'">') !== false && strpos($html, $messages['checkout']) !== false, 'render checkout ' . $locale);
    langCheck(strpos($html, '{$') === false && strpos($html, 'const i18n = {') !== false, 'template and JavaScript dictionary ' . $locale);
    $errorHtml = View::fetch(dirname(__DIR__).'/app/openapi/view/pay/error.html', ['msg'=>$messages['订单不存在']]);
    langCheck(strpos(html_entity_decode($errorHtml, ENT_QUOTES, 'UTF-8'),$messages['system_notice']) !== false && strpos(html_entity_decode($errorHtml, ENT_QUOTES, 'UTF-8'),$messages['订单不存在']) !== false, 'render error page ' . $locale);
    $app->http->name('admin');
    langCheck(AjaxUtils::error('订单不存在')->getData()['msg'] === '订单不存在', 'admin language unaffected');
    $app->http->name('openapi');
}
Db::name('config')->where('key','openapi_language')->update(['content'=>'invalid']);
$setLanguage();
langCheck(Lang::getLangSet() === 'zh-cn', 'invalid language falls back to Chinese');
Db::name('config')->delete(true);
$setLanguage();
langCheck(Lang::getLangSet() === 'zh-cn', 'missing configuration falls back to Chinese');
