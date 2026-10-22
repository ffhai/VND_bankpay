<?php
require dirname(__DIR__) . '/vendor/autoload.php';
use app\common\utils\RateUtils;
use app\common\exception\RequestException;
function checkRate($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo 'PASS ' . $message . PHP_EOL;
}
checkRate(RateUtils::normalize('1.25') === '1.2500', 'four decimal percent');
checkRate(RateUtils::fee(10000, '1.2500') === 125, 'percent conversion');
checkRate(RateUtils::fee(50, '1') === 1, 'half rounds up');
checkRate(RateUtils::fee(49, '1') === 0, 'below half rounds down');
checkRate(RateUtils::fee(25 + 25, '1') === 1, 'aggregate before rounding');
checkRate(RateUtils::fee(9999999999999, '99.9999') === 9999989999999, 'large integer calculation remains exact');
checkRate(RateUtils::fee(9999999999999, '100') === 9999999999999, '100 percent');
checkRate(RateUtils::fee(100, '0') === 0, 'zero rate');
foreach (['-1', '100.0001', '1.23456', '1e1', '', null, [], true] as $value) {
    $rejected = false;
    try { RateUtils::normalize($value); } catch (RequestException $e) { $rejected = true; }
    checkRate($rejected, 'invalid rate rejected');
}
