import configparser
import json
from pathlib import Path
import re
import time


STATUS_PROCESSED = 2
STATUS_FAILED = -1


def log(message):
    print("[设备通知解码]%s %s" % (
        time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()), message
    ))


def decode_digibank(notify_id, text):
    """解析交易内容，仅返回短信交易表需要的四个上传字段。"""
    if not isinstance(text, str):
        return None

    balance = re.search(r'S[oố]\s*d[uư]\s*:\s*(-?[0-9][0-9,]*)\s*VND', text, re.IGNORECASE)
    if balance is None:
        balance = re.search(r'(?<![0-9,])(-?[0-9][0-9,]*)\s*VND', text)
    bank_number = re.search(r'ND:\s*([0-9]{1,20})(?=-|\s|$)', text)
    remark = re.search(r'-([A-Za-z0-9]+)\s*$', text)
    if balance is None or bank_number is None:
        return None

    amount = int(balance.group(1).replace(',', ''))
    reference = remark.group(1) if remark else ''
    # 按现有 dt_sms_trade 的 int / varchar 字段范围上传，不扩展表结构。
    if not 0 <= amount <= 2147483647 or len(reference) > 255:
        return None
    return {
        'notify_id': notify_id,
        'bank_number': bank_number.group(1),
        'balance': amount,
        'remark': reference,
    }


def process_notification(item, api):
    notify_id = item['id']
    try:
        content = json.loads(item.get('content', ''))
        if not isinstance(content, dict):
            raise ValueError('content 必须是对象')
    except (TypeError, ValueError):
        log('[%s]content解析失败' % notify_id)
        return api.setPending({'id': notify_id, 'status': STATUS_FAILED})

    if content.get('package_name') != 'com.msb.digibank.retail':
        # 非交易通知不再向已移除的 sms_code 等字段写入数据。
        return api.setPending({'id': notify_id, 'status': STATUS_PROCESSED})

    text = content.get('extrasText') or content.get('extrasBigText') or content.get('text') or content.get('tickerText') or ''
    data = decode_digibank(notify_id, text)
    if data is None:
        log('[%s]交易识别失败' % notify_id)
        return api.setPending({'id': notify_id, 'status': STATUS_FAILED})

    result = api.addSmsTrade(data)
    # 入库成功后由接收端更新推送状态；上传失败时保留未处理状态，等待重试。
    log('[%s]交易上传返回：%s' % (notify_id, json.dumps(result, ensure_ascii=False)))
    return result


def main():
    from modules.api import Api

    config = configparser.ConfigParser()
    config.read(Path(__file__).with_name('config.ini'), encoding='utf-8')
    api = Api(config.get('api', 'base_url'), config.get('api', 'appid'), config.get('api', 'secret'))

    while True:
        try:
            result = api.getPending()
            if isinstance(result, dict) and result.get('code') == 1:
                for item in result.get('data', []):
                    try:
                        process_notification(item, api)
                    except Exception as error:
                        log('[%s]处理异常：%s' % (item.get('id', ''), type(error).__name__))
        except Exception as error:
            log('获取推送异常：%s' % type(error).__name__)
        time.sleep(1)


if __name__ == '__main__':
    main()
