<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'app\command\getbanks',
        'app\command\OrderExpired',
        'app\command\OrderSettlement',
    ],
];
