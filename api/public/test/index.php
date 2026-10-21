<?php
include_once 'pay.class.php';
$amount = isset($_POST['amount'])?$_POST['amount']:0;
$pay_type = isset($_POST['pay_type'])?$_POST['pay_type']:'';
$bank_code = isset($_POST['bank_code'])?$_POST['bank_code']:'';
$order_id = isset($_GET['order_id'])?$_GET['order_id']:'';

$cmd = isset($_GET['cmd'])?$_GET['cmd']:'';

$request = 'http://4.test.com';

$pay = new Pay($request,'test_merchant','m3vokxdps98e34jwwzhy5');
if($amount > 0){
    $params=[];
    $params['trade_no'] = time();//商户订单号
    $params['amount'] = $amount;//付款金额
    $params['pay_type'] = $pay_type;
    $params['bank_code'] = $bank_code;
    $params['notify_url'] = $request.'/test/notify.php';//回调地址
    $params['return_url'] = $request.'/test/return.php';//支付成功跳转
    $order = $pay->createOrder($params);
    $order = json_decode($order,true);
    if($order['code'] == 1){
        header('Location:'.$order['data']['pay_url']);//跳转付款连接
    }else{
        exit($order['msg']);//创建订单失败
    }
}
if(!empty($order_id)){
    //查询订单
    exit($pay->queryOrder($order_id));
}
if($cmd == 'banks'){
    //银行列表
    exit($pay->banks());
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>支付页面 · H5</title>
    <style>
        /* 全局重置 & 基础样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f5f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* 卡片容器 — 模拟移动端支付卡片 */
        .payment-card {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06), 0 8px 20px rgba(0, 0, 0, 0.03);
            padding: 32px 24px 28px;
            transition: all 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        /* 头部 */
        .payment-header {
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .payment-header h1 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.3px;
            color: #0b1b32;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .payment-header h1 span {
            background: #eef2f7;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            margin-left: 6px;
        }

        .badge-safe {
            background: #e8f0fe;
            color: #1a5c9e;
            font-size: 13px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 30px;
            letter-spacing: 0.3px;
        }

        /* 表单元素通用 */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            color: #1f2a44;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

        .form-label .required {
            color: #e44c4c;
            margin-left: 2px;
        }

        /* 金额输入框 — 带货币符号 */
        .amount-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: #f8fafd;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            transition: border-color 0.15s ease;
        }

        .amount-input-wrapper:focus-within {
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
        }

        .currency-symbol {
            position: absolute;
            left: 18px;
            font-size: 20px;
            font-weight: 500;
            color: #1f2a44;
            pointer-events: none;
        }

        .amount-input-wrapper input {
            width: 100%;
            padding: 18px 18px 18px 44px;
            font-size: 20px;
            font-weight: 500;
            border: none;
            background: transparent;
            border-radius: 20px;
            outline: none;
            color: #0b1b32;
            letter-spacing: 0.5px;
            font-variant-numeric: tabular-nums;
        }

        .amount-input-wrapper input::placeholder {
            color: #a8b6ce;
            font-weight: 400;
            font-size: 18px;
            letter-spacing: 0.2px;
        }

        /* 自定义下拉选择器 — 完全原生风格，但美化 */
        .select-wrapper {
            position: relative;
            background: #f8fafd;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            transition: border-color 0.15s ease;
        }

        .select-wrapper:focus-within {
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
        }

        .select-wrapper select {
            width: 100%;
            padding: 18px 48px 18px 20px;
            font-size: 18px;
            font-weight: 450;
            background: transparent;
            border: none;
            border-radius: 20px;
            outline: none;
            color: #0b1b32;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            font-family: inherit;
        }

        /* 自定义下拉箭头 (伪元素) */
        .select-wrapper::after {
            content: "▾";
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #6f7d98;
            pointer-events: none;
            transition: color 0.2s;
        }

        .select-wrapper:hover::after {
            color: #1f2a44;
        }

        /* 下拉选项 — 适配移动端 */
        .select-wrapper select option {
            padding: 14px 12px;
            font-size: 17px;
            background: #ffffff;
            color: #0b1b32;
        }

        /* 提交按钮 — 纯粹表单提交 */
        .submit-btn {
            width: 100%;
            padding: 18px 12px;
            background: #1a5c9e;
            border: none;
            border-radius: 40px;
            color: white;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.6px;
            cursor: pointer;
            transition: background 0.2s, transform 0.05s;
            box-shadow: 0 6px 14px rgba(26, 92, 158, 0.25);
            margin-top: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-family: inherit;
        }

        .submit-btn:hover {
            background: #13467a;
            box-shadow: 0 8px 18px rgba(26, 92, 158, 0.3);
        }

        .submit-btn:active {
            transform: scale(0.98);
            background: #0e3a63;
        }

        /* 辅助信息/安全提示 */
        .secure-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            font-size: 14px;
            color: #6b7b96;
            letter-spacing: 0.2px;
        }

        .secure-note svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #6b7b96;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .secure-note span {
            background: #eef2f7;
            padding: 2px 12px;
            border-radius: 40px;
            font-weight: 450;
            color: #2c3e50;
        }

        /* 底部留白 */
        .form-footer {
            margin-top: 6px;
            border-top: 1px solid #edf2f7;
            padding-top: 18px;
            text-align: center;
            font-size: 13px;
            color: #8a9bb0;
        }

        /* 简单的禁用状态 (无js) 仅仅视觉 */
        .select-wrapper select:disabled,
        .amount-input-wrapper input:disabled {
            opacity: 0.7;
        }

        /* 针对小屏优化 */
        @media (max-width: 480px) {
            .payment-card {
                padding: 24px 18px 20px;
                border-radius: 28px;
            }
            .payment-header h1 {
                font-size: 22px;
            }
            .amount-input-wrapper input {
                font-size: 18px;
                padding: 16px 16px 16px 42px;
            }
            .currency-symbol {
                left: 16px;
                font-size: 18px;
            }
            .select-wrapper select {
                font-size: 17px;
                padding: 16px 44px 16px 18px;
            }
            .submit-btn {
                font-size: 19px;
                padding: 16px 12px;
            }
        }

        /* 移除number输入框的上下箭头（保留功能） */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body>
<div class="payment-card">
    <!-- 头部 -->
    <div class="payment-header">
        <h1>
            💳 支付
            <span>H5</span>
        </h1>
        <div class="badge-safe">🔒 安全</div>
    </div>

    <form action="" method="POST">
        <!-- 金额输入 -->
        <div class="form-group">
            <label class="form-label" for="amount">
                支付金额 <span class="required">*</span>
            </label>
            <div class="amount-input-wrapper">
                <span class="currency-symbol">¥</span>
                <input type="number" id="amount" name="amount" placeholder="请输入整数金额" required >
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="amount">
                指定银行
            </label>
            <div class="amount-input-wrapper">
                <input id="bank_code" name="bank_code" placeholder="请输入指定银行代码，可空"  >
            </div>
        </div>

        <!-- 支付方式下拉选择 -->
        <div class="form-group">
            <label class="form-label" for="paymentMethod">
                支付方式 <span class="required">*</span>
            </label>
            <div class="select-wrapper">
                <select id="paymentMethod" name="pay_type" required aria-required="true">
                    <option value="bank2bank">bank2bank</option>
                </select>
            </div>
            <!-- 辅助说明 -->
            <div style="margin-top: 6px; font-size: 13px; color: #7f8fa4; padding-left: 4px;">
                微信 / 支付宝 扫码或跳转
            </div>
        </div>

        <!-- 提交按钮 — 原生表单提交，无 JS 拦截 -->
        <button type="submit" class="submit-btn">
            🔐 确认支付
        </button>
    </form>
</div>
</body>
</html>

