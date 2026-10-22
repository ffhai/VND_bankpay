<?php

declare (strict_types = 1);

namespace app\common\service;

use app\common\exception\RequestException;
use app\common\model\UploadModel;
use think\facade\Filesystem;
use think\facade\Log;
use think\file\UploadedFile;

class UploadService
{
    public static function validateFile($file, array $settings): array
    {
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            throw new RequestException('请选择一个有效文件，或检查文件是否超过服务器上传限制');
        }
        $extension = strtolower($file->getOriginalExtension());
        $allowed = $settings['types'] ?? [];
        if (!isset($allowed[$extension])) {
            throw new RequestException('不允许上传此文件格式');
        }
        $size = $file->getSize();
        if ($size <= 0 || $size > $settings['max_size']) {
            throw new RequestException('文件不能为空且不能超过 ' . ($settings['max_size'] / 1024 / 1024) . 'MB');
        }
        $mime = strtolower($file->getMime());
        if (!in_array($mime, array_map('strtolower', $allowed[$extension]), true)) {
            throw new RequestException('文件内容与扩展名不匹配');
        }
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
        if ($isImage && @getimagesize($file->getRealPath()) === false) {
            throw new RequestException('图片文件无效');
        }
        return [
            'name' => mb_substr(basename(str_replace('\\', '/', $file->getOriginalName())), 0, 255),
            'size' => $size,
            'mime' => $mime,
            'type' => $isImage ? 'image' : 'file',
        ];
    }

    public static function upload($file): array
    {
        $data = self::validateFile($file, config('upload'));
        $disk = Filesystem::disk('public');
        $saved = $disk->putFile('uploads', $file);
        if (!$saved) {
            throw new RequestException('文件保存失败，请稍后重试');
        }
        $data['path'] = rtrim(Filesystem::getDiskConfig('public', 'url'), '/') . '/' . str_replace('\\', '/', $saved);
        $data['time'] = time();
        try {
            if (!(new UploadModel())->insert($data)) {
                throw new RequestException('附件记录保存失败，请稍后重试');
            }
        } catch (\Throwable $e) {
            try {
                $disk->delete($saved);
            } catch (\Throwable $cleanupError) {
                Log::error('上传回滚清理失败: ' . $saved . ' ' . $cleanupError->getMessage());
            }
            throw $e;
        }
        $path = $data['path'];
        $data['url'] = preg_match('~^https?://~i', $path) ? $path : rtrim(request()->domain(), '/') . '/' . ltrim($path, '/');
        return $data;
    }
}
