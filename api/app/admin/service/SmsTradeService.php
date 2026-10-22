<?php
declare(strict_types=1);
namespace app\admin\service;

use app\common\basics\Service;
use app\common\exception\RequestException;
use app\common\model\SmsTradeModel;

class SmsTradeService extends Service
{
    public static function lists(array $get, array $userItem): array
    {
        $where = self::setSearch([
            '%like%' => ['bank_number', 'order_id', 'remark'],
            '=' => ['id', 'notify_id', 'status'],
        ], $get);
        $lists = (new SmsTradeModel())->where($where)->order('id desc')->paginate([
            'page' => $get['page'] ?? 1,
            'list_rows' => $get['pagesize'] ?? 20,
        ])->toArray();
        return ['count' => $lists['total'], 'items' => $lists['data']];
    }

    public static function del(array $ids, array $userItem): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) { return $id > 0; })));
        if (!$ids) {
            throw new RequestException('请选择短信交易记录');
        }
        SmsTradeModel::destroy($ids, true);
    }
}
