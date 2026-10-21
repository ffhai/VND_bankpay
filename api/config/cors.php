<?php

declare(strict_types=1);

// 跨域配置
return [
    // 允许的跨域来源，多个用英文逗号分隔，例如：https://admin.example.com,http://localhost:3001
    // 留空表示回显请求的 Origin（仅用于本地开发，不发送 Allow-Credentials）
    'allow_origins' => (string) env('cors.allow_origins', ''),
];
