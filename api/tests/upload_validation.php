<?php
// php api/tests/upload_validation.php
require dirname(__DIR__) . '/vendor/autoload.php';

use app\common\exception\RequestException;
use app\common\service\UploadService;
use think\file\UploadedFile;

$settings = require dirname(__DIR__) . '/config/upload.php';
$tempFiles = [];
$makeFile = function ($name, $content) use (&$tempFiles) {
    $path = tempnam(sys_get_temp_dir(), 'upload_test_');
    $tempFiles[] = $path;
    file_put_contents($path, $content);
    return new UploadedFile($path, $name, null, UPLOAD_ERR_OK, true);
};
$reject = function ($file, $config) {
    try {
        UploadService::validateFile($file, $config);
    } catch (RequestException $e) {
        return;
    }
    throw new RuntimeException('Expected upload rejection');
};
try {
    $text = $makeFile('report.TXT', "Sample report\n");
    $data = UploadService::validateFile($text, $settings);
    if ($data['name'] !== 'report.TXT' || $data['type'] !== 'file') {
        throw new RuntimeException('Original filename or file type incorrect');
    }
    UploadService::validateFile($makeFile('report.csv', "name,value\nexample,123\n"), $settings);
    UploadService::validateFile($makeFile('report.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF"), $settings);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
    $data = UploadService::validateFile($makeFile('image.png', $png), $settings);
    if ($data['type'] !== 'image') throw new RuntimeException('Expected image');
    $reject(null, $settings);
    $reject([$text], $settings);
    $reject($makeFile('empty.txt', ''), $settings);
    $reject($makeFile('script.php', '<?php echo 1;'), $settings);
    $reject($makeFile('fake.jpg', "Plain text, not an image"), $settings);
    $reject($makeFile('fake.zip', "Plain text, not an archive"), $settings);
    $limited = $settings;
    $limited['max_size'] = 1;
    $reject($text, $limited);
    echo "Upload validation checks passed\n";
} finally {
    foreach ($tempFiles as $path) unlink($path);
}
