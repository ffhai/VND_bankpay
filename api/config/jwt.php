<?php

declare(strict_types=1);

// JWT 配置
return [
    // 签名密钥：优先从 .env 读取（[JWT] SECRET = xxx），生产环境务必配置固定值。
    // 未配置时，LoginService 会自动生成并持久化到根 runtime 目录，保证重启后不变。
    'secret' => env('jwt.secret', ''),

    // token 有效期（秒），默认 7 天
    'expire' => 3600 * 24 * 7,
];
