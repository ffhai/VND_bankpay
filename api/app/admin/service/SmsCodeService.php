<?php
declare(strict_types=1);
namespace app\admin\service;

use app\common\basics\Service;
use app\common\exception\RequestException;
use app\common\model\SmsCodeModel;
use app\common\utils\TimeUtils;

class SmsCodeService extends Service
{
    public static function lists(array $get, array $userItem): array
    {
        $where = self::setSearch([
            '%like%' => ['phone_number', 'verify_code'],
            '=' => ['id', 'notify_id'],
            'datetime' => ['time'],
        ], $get);
        $lists = SmsCodeModel::where($where)->order('id desc')->paginate([
            'page' => $get['page'] ?? 1,
            'list_rows' => $get['pagesize'] ?? 20,
        ])->toArray();
        foreach ($lists['data'] as &$item) {
            $item['time'] = TimeUtils::formatTime($item['time']);
        }
        return ['count' => $lists['total'], 'items' => $lists['data']];
    }

    public static function del(array $ids, array $userItem): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) { return $id > 0; })));
        if (!$ids) {
            throw new RequestException('请选择短信验证记录');
        }
        SmsCodeModel::destroy($ids, true);
    }
}
