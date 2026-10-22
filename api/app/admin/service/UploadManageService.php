<?php

declare (strict_types = 1);

namespace app\admin\service;

use app\common\basics\Service;
use app\common\exception\RequestException;
use app\common\model\UploadModel;
use app\common\utils\TimeUtils;
use think\facade\Filesystem;

class UploadManageService extends Service
{
    public static function lists(array $params, array $userItem = []): array
    {
        $where = self::setSearch([
            '%like%' => ['keyword@name'],
            '=' => ['type@type'],
            'datetime' => ['time@time'],
        ], $params);
        $list = UploadModel::where($where)->order('id', 'desc')->paginate([
            'page' => max(1, (int) ($params['page'] ?? 1)),
            'list_rows' => min(100, max(1, (int) ($params['pagesize'] ?? 20))),
        ])->toArray();
        foreach ($list['data'] as &$item) {
            $path = $item['path'];
            $item['url'] = preg_match('~^https?://~i', $path) ? $path : rtrim(request()->domain(), '/') . '/' . ltrim($path, '/');
            $item['time'] = TimeUtils::formatTime($item['time']);
        }
        unset($item);
        return ['items' => $list['data'], 'count' => $list['total']];
    }

    // 只允许删除 public 磁盘中的附件，不接受客户端提供的路径。
    public static function diskPath(string $path, string $base): string
    {
        $prefix = rtrim($base, '/') . '/';
        if (strpos($path, $prefix) !== 0) {
            throw new RequestException('附件路径不属于上传目录');
        }
        $relative = substr($path, strlen($prefix));
        if ($relative === '' || preg_match('~[\\\\\x00-\x1f]|(^|/)\.\.?(/|$)|[:?#%]~', $relative) || $relative[0] === '/') {
            throw new RequestException('附件路径无效');
        }
        return $relative;
    }

    public static function del(array $ids, array $userItem = []): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) { return $id > 0; })));
        if (!$ids) throw new RequestException('请选择要删除的文件');
        $disk = Filesystem::disk('public');
        $base = (string) Filesystem::getDiskConfig('public', 'url');
        $rows = UploadModel::whereIn('id', $ids)->select();
        // 全部路径先检查，避免混入非法路径时已删除其他选中项。
        $paths = [];
        foreach ($rows as $row) {
            $relative = self::diskPath($row['path'], $base);
            if (Filesystem::getDiskConfig('public', 'type') === 'local') {
                $root = realpath($disk->path(''));
                $target = realpath($disk->path($relative));
                if ($target !== false && ($root === false || strpos(str_replace('\\', '/', $target), rtrim(str_replace('\\', '/', $root), '/') . '/') !== 0)) {
                    throw new RequestException('附件路径超出上传目录');
                }
            }
            $paths[$row['id']] = $relative;
        }
        foreach ($rows as $row) {
            $relative = $paths[$row['id']];
            try {
                if ($disk->fileExists($relative)) {
                    $disk->delete($relative);
                }
            } catch (\League\Flysystem\FilesystemException $e) {
                throw new RequestException('文件删除失败，请稍后重试', 0, [], $e);
            }
            $row->delete();
        }
    }
}
