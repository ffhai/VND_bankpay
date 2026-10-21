<?php
declare(strict_types=1);
namespace app\common\service;

use app\common\enums\StatusEnum;
use app\common\exception\RequestException;
use app\common\model\BankCardsModel;
use app\common\model\BankUserModel;
use app\common\model\BanksModel;
use app\common\model\OrderModel;
use think\facade\Db;

class BankDataService
{
    // Call inside the write transaction; lock references before the card.
    public static function validateCard(array $post, int $ownerId, int $cardId = 0): void
    {
        foreach (['balance', 'limit_in_max', 'limit_out_max'] as $field) {
            \app\common\utils\AmountUtils::integer($post[$field] ?? 0);
        }
        $bank = BanksModel::where('code', $post['bank_code'] ?? '')->lock(true)->find();
        $holder = BankUserModel::where('id', (int) ($post['bank_user_id'] ?? 0))->lock(true)->find();
        if (!$bank || !preg_match('/^\d{6}$/D', (string) $bank['bin'])) {
            throw new RequestException('银行不存在或银行BIN无效');
        }
        if (!$holder || (int) $holder['uid'] !== $ownerId) {
            throw new RequestException('银行户名不存在或不属于当前银行卡所属渠道');
        }
        if ((int) ($post['status'] ?? -1) === StatusEnum::STATUS_1
            && ((int) $bank['status'] !== StatusEnum::STATUS_1 || (int) $holder['status'] !== StatusEnum::STATUS_1)) {
            throw new RequestException('启用银行卡前，请先启用所属银行及户名');
        }
        if (!preg_match('/^\d{6,19}$/D', (string) ($post['bank_number'] ?? ''))) {
            throw new RequestException('卡号必须为6至19位数字');
        }
        if ($cardId) {
            $card = BankCardsModel::where('id', $cardId)->lock(true)->find();
            if (!$card || (int) $card['uid'] !== $ownerId) {
                throw new RequestException('银行卡不存在或无权限操作');
            }
            if ($card['bank_number'] !== $post['bank_number'] && OrderModel::where(function ($q) use ($card) {
                $q->where('bank_card_id', $card['id'])->whereOr('bank_number', $card['bank_number']);
            })->find()) {
                throw new RequestException('银行卡已有订单引用，不可修改卡号，请另建新卡');
            }
        }
        // Existing notification APIs identify a card by number alone: uniqueness is GLOBAL.
        if (BankCardsModel::where('bank_number', $post['bank_number'])->where('id', '<>', $cardId)->find()) {
            throw new RequestException('卡号已在系统中登记，不可跨渠道或跨银行重复添加');
        }
    }

    public static function deleteOrDisable(string $kind, array $ids, ?int $ownerId = null): array
    {
        $models = ['bank'=>BanksModel::class, 'holder'=>BankUserModel::class, 'card'=>BankCardsModel::class];
        if (!isset($models[$kind])) { throw new RequestException('不支持的基础资料类型'); }
        return Db::transaction(function () use ($models, $kind, $ids, $ownerId) {
            $class = $models[$kind];
            $query = $class::where('id', 'in', $ids)->order('id');
            if ($ownerId !== null && $kind !== 'bank') { $query->where('uid', $ownerId); }
            $rows = $query->lock(true)->select();
            $result = ['disabled'=>0, 'deleted'=>0];
            foreach ($rows as $row) {
                if ($kind === 'card') {
                    $referenced = OrderModel::where(function ($q) use ($row) {
                        $q->where('bank_card_id', $row['id'])->whereOr('bank_number', $row['bank_number']);
                    })->find();
                } elseif ($kind === 'holder') {
                    $referenced = OrderModel::where('bank_user_id', $row['id'])->find()
                        || BankCardsModel::where('bank_user_id', $row['id'])->find();
                } else {
                    $referenced = OrderModel::where('bank_code', $row['code'])->find()
                        || BankCardsModel::where('bank_code', $row['code'])->find();
                }
                if ($referenced) {
                    $changes = ['status'=>StatusEnum::STATUS_OFF, 'update_time'=>time()];
                    if ($kind === 'bank') { $changes += ['status_in'=>StatusEnum::STATUS_OFF, 'status_out'=>StatusEnum::STATUS_OFF]; }
                    $row->save($changes);
                    $result['disabled']++;
                } else {
                    $row->delete();
                    $result['deleted']++;
                }
            }
            return $result;
        });
    }
}
