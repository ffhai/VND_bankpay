<?php
// php api/tests/upload_management.php
require dirname(__DIR__) . '/vendor/autoload.php';
use app\admin\service\UploadManageService;
use app\common\exception\RequestException;

foreach (['/storage/uploads/a.pdf' => 'uploads/a.pdf', '/storage/topic/2020/a.jpg' => 'topic/2020/a.jpg'] as $path => $expected) {
    if (UploadManageService::diskPath($path, '/storage') !== $expected) throw new RuntimeException('Valid path rejected');
}
foreach (['/storage/../a', '/storage/a/../../file', '/other/a', '/storage/', '/storage//absolute', '/storage/a\\..\\b', '/storage/%2e%2e/file', 'https://evil.test/storage/a', '/storage/a?x', "/storage/a\0b"] as $path) {
    try {
        UploadManageService::diskPath($path, '/storage');
    } catch (RequestException $e) {
        continue;
    }
    throw new RuntimeException('Unsafe path accepted: ' . $path);
}
echo "Upload management path checks passed\n";
