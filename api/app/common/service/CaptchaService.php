<?php
declare(strict_types=1);

namespace app\common\service;

use think\Cache;
use think\captcha\Captcha;
use think\Config;
use think\Session;

/**
 * 使用官方绘图组件，保留前后端分离的 key + base64 协议。
 * 答案仅存服务端缓存，不依赖 Session cookie。
 */
class CaptchaService extends Captcha
{
    private Cache $cache;
    private string $captchaKey = '';

    public function __construct(Config $config, Cache $cache, Session $session)
    {
        // generate/check 已改用缓存；父类绘图仍需要初始化配置对象。
        parent::__construct($config, $session);
        $this->cache = $cache;
    }

    protected function configure(?string $config = null): void
    {
        parent::configure($config);
        $this->api = false;
    }

    protected function generate(): array
    {
        if ($this->math) {
            $this->useZh = false;
            $this->length = 5;
            $x = random_int(10, 30);
            $y = random_int(1, 9);
            $value = "$x + $y = ";
            $answer = (string) ($x + $y);
        } else {
            $characters = $this->useZh
                ? preg_split('//u', $this->zhSet, -1, PREG_SPLIT_NO_EMPTY)
                : str_split($this->codeSet);
            $value = '';
            for ($i = 0; $i < $this->length; $i++) {
                $value .= $characters[random_int(0, count($characters) - 1)];
            }
            $answer = mb_strtolower($value, 'UTF-8');
        }

        $this->captchaKey = bin2hex(random_bytes(32));
        $this->cache->set('captchaApi.' . $this->captchaKey, password_hash($answer, PASSWORD_BCRYPT), (int) $this->expire);

        return ['value' => $value, 'key' => $this->captchaKey];
    }

    public function create(?string $config = null): array
    {
        $image = parent::create($config);

        return [
            'key' => $this->captchaKey,
            'base64' => 'data:image/png;base64,' . base64_encode($image->getContent()),
        ];
    }

    public function check(string $code, string $key = ''): bool
    {
        if ($code === '' || !preg_match('/^[a-f0-9]{64}$/D', $key)) {
            return false;
        }
        $cacheKey = 'captchaApi.' . $key;
        $hash = $this->cache->get($cacheKey);
        if (!is_string($hash) || !password_verify(mb_strtolower($code, 'UTF-8'), $hash)) {
            return false;
        }
        $this->cache->delete($cacheKey);

        return true;
    }
}
