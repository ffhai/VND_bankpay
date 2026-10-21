from modules.httpHelper import HttpClient
import json
from modules.api import Api
import time
import threading
import configparser
import re

config = configparser.ConfigParser()
config.read('config.ini', encoding='utf-8')

BASE_URL = config.get('api','base_url')
APPID = config.get('api','appid')
SECRET = config.get('api','secret')

api = Api(BASE_URL,APPID,SECRET)

def log(str):
    str = "[设备通知解码]%s %s" % (time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()), str)
    print(str)


def decode_digibank(id,text):
    result = {
        "id":str(id),
        "type":"BANK_TRADE",
        "balance": 0,
        "card_number": '',
        "remark": '',
    }

    m = re.search(r'([\d,]+)\s*VND', text)
    if m:
        result["balance"] = int(m.group(1).replace(",", ""))

    m = re.search(r'ND:\s*(\d+)', text)
    if m:
        result["card_number"] = m.group(1)

    m = re.search(r'-([A-Za-z0-9]+)\s*$', text)
    if m:
        result["remark"] = m.group(1)

    log('['+str(id)+']'+json.dumps(result))
    if result["balance"] == 0 and result["card_number"] == '' and  result["remark"] == '':
        log('['+str(id)+']'+"识别失败")
    
    ret = api.setPending(result)
    log('['+str(id)+']'+"上传返回：" + json.dumps(ret, ensure_ascii=False))

#匹配短信验证码
def _extract_otp(sms_text: str) -> str:
  """提取不同短信格式中的验证码

  :param sms_text: 短信正文内容
  :return: 提取到的验证码字符串，未找到返回 None
  """
  # 策略 1：匹配带有明确 OTP/Ma xac thuc 关键词后面的 4 到 8 位数字
  # 兼容: "Ma OTP la 535928", "ma xac thuc: 123456", "OTP: 9876" 等
  pattern_keyword = r"(?:otp|ma\s*xac\s*thuc|ma\s*otp|code)[\s:]*([0-9]{4,8})"
  match = re.search(pattern_keyword, sms_text, re.IGNORECASE)
  if match:
    return match.group(1)

  # 策略 2：如果没有明确关键词，匹配短信中独立的 4 到 6（或 8）位数字
  # 通常验证码是独立的数字串，前后可能是空格或标点符号
  # 排除掉类似年份 (2026) 或纯长串数字
  candidates = re.findall(
      r"\b\d{4,8}\b", sms_text
  )  # 匹配独立的 4 到 8 位数字
  if candidates:
    # 如果有多个数字，通常验证码是最像的一个（例如 6 位或 4 位数字）
    # 这里可以根据常见习惯优先返回 6 位或 4 位的数字
    for num in candidates:
      if len(num) in [4, 5, 6]:
        return num
    return candidates[0]  # 默认返回找到的第一个

  return None

def decode_sms(id,text):
    result = {
        "id":id,
        "type":"SMS_CODE",
        "sms_code": ""
    }
    result["sms_code"] = _extract_otp(text)
    if result["sms_code"]:
        pass
    else:
        log('['+str(id)+']'+"未匹配到验证码")
    ret = api.setPending(result)
    log('['+str(id)+']'+"上传返回：" + json.dumps(ret, ensure_ascii=False))
def decode_other(id,text):
    result = {
        "id":id,
        "type":"OTHER",
    }
    ret = api.setPending(result)
    log('['+str(id)+']'+"上传返回：" + json.dumps(ret, ensure_ascii=False))    
    


while True:
    obj = api.getPending()

    code = obj.get('code',0)
    if code == 0:
        log("解析失败")

    if code == 1:
        items = obj.get('data',[])
        for item in items:
            id = item.get('id',0)
            content = item.get('content','')
            try:
                content = json.loads(content)
            except json.JSONDecodeError as e:
                log("content解析失败")
            
            package_name = content.get('package_name','')
            title = content.get('title','')
            text = content.get('text','')
            when = content.get('when','')
            tickerText = content.get('tickerText','')
            extrasText = content.get('extrasText','')
            extrasBigText = content.get('extrasBigText','')
            
            if package_name == 'com.msb.digibank.retail':
                decode_digibank(id,extrasText)
                continue

            if package_name == 'com.google.android.apps.messaging':
                decode_sms(id,tickerText)
                continue

            decode_other(id,extrasText)

    else:
        time.sleep(1)

    #exit()

    