<?php

declare (strict_types = 1);

namespace app\common\service;


use app\common\basics\Service;
use app\common\model\BankCardsModel;
use app\common\exception\RequestException;

class BankCardsService extends Service
{
    public static function testVietqr(array $params, array $userItem, bool $ownOnly = false): array
    {
        $id = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        $amount = filter_var($params['amount'] ?? null, FILTER_VALIDATE_INT);
        $remark = trim((string) ($params['remark'] ?? ''));
        if (!$id || $id < 1) {
            throw new RequestException('请选择银行卡');
        }
        if ($amount === false || $amount < 1 || $amount > 9999999999999) {
            throw new RequestException('金额必须为正整数，最多13位');
        }
        if (!preg_match('/^[A-Za-z0-9 ]{0,25}$/D', $remark)) {
            throw new RequestException('附言最多25位，仅支持英文字母、数字或空格');
        }
        $query = BankCardsModel::where('id', $id);
        if ($ownOnly) {
            $query->where('uid', (int) $userItem['id']);
        }
        $card = $query->with(['bankinfo', 'bankuser'])->find();
        if (!$card) {
            throw new RequestException('银行卡不存在或无权限操作');
        }
        if (empty($card['bankinfo']['bin']) || empty($card['bankuser']['name'])) {
            throw new RequestException('请先完善银行卡的银行及户名信息');
        }
        try {
            $result = (new \Vietqr())->generate([
                'accountNo' => $card['bank_number'],
                'accountName' => $card['bankuser']['name'],
                'acqId' => $card['bankinfo']['bin'],
                'addInfo' => $remark,
                'amount' => $amount,
            ]);
        } catch (\Throwable $e) {
            throw new RequestException('VietQR请求失败，请检查服务配置或稍后重试');
        }
        if (!$result || ($result['code'] ?? '') !== '00' || empty($result['data']['qrDataURL'])) {
            throw new RequestException('VietQR生成失败：' . ($result['desc'] ?? '服务未返回二维码'));
        }
        return ['qrcode' => $result['data']['qrDataURL']];
    }

    public static function getItemByBankNumber(string $bank_number){
        $model = new BankCardsModel();
        $item = $model->where('bank_number','=',$bank_number)->find();
        if($item){
            return $item->toArray();
        }
        return false;
    }
    public static function getItemById(int $id){
        $model = new BankCardsModel();
        $item = $model->where('id','=',$id)->find();
        if($item){
            return $item->toArray();
        }
        return false;
    }
}
