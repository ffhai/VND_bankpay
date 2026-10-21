from modules.httpHelper import HttpClient
from modules.jsonHelper import JsonHelper
from modules.api import Api
import time
import threading
import configparser

config = configparser.ConfigParser()
config.read('config.ini', encoding='utf-8')

BASE_URL = config.get('api','base_url')
APPID = config.get('api','appid')
SECRET = config.get('api','secret')

api = Api(BASE_URL,APPID,SECRET)

def log(str):
    str = "[自动回调]%s %s" % (time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()), str)
    print(str)

def threadNotify(data):
    order_id = JsonHelper.get_value(data,'order_id')
    url = JsonHelper.get_value(data,'url')
    print(url)

    count = 0
    while True:
        count += 1
        if count > 10:
            log("%s 第 %d 次回调失败" % (order_id, count))
            api.setNotifyStatus(order_id,-1)
            break

        log("%s 第 %d 次回调" % (order_id, count))
        client = HttpClient(verify_ssl=False)
        resp = client.get(url)
        if resp.is_success != True:
            log("%s 第 %d 次回调：%s" % (order_id, count,"请求出错"))
            time.sleep(count * 5)
        else:
            if 'success' in resp.data or 'ok' in resp.data:
                log("%s 第 %d 次回调成功" % (order_id, count))
                api.setNotifyStatus(order_id,2)
                break
    

while True:
    data = api.getNotifyTask()
    #print(data)
    code = JsonHelper.get_int(data,'code')
    if code == 1:
        task_id = JsonHelper.get_string(data,'data','task_id')
        expire_time = JsonHelper.get_int(data,'data','expire_time')
        data = JsonHelper.get_value(data,'data','data')
        log('TASK_ID：%s' % task_id)
        threading.Thread(target=threadNotify,args=(data,)).start()
    else:
        time.sleep(1)

    