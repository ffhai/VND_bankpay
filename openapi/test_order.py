from modules.httpHelper import HttpClient
from modules.jsonHelper import JsonHelper
from modules.api import Api
import time
import logging
import os

api = Api()
logger = logging.getLogger(__name__)

def log(str):
    str = "[自动回调]%s %s" % (time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()), str)
    print(str)

    filename = time.strftime("%Y%m%d%H", time.localtime())
    path = './log'
    if not os.path.exists(path):
        os.makedirs(path)
    logging.basicConfig(filename='%s/order_%s.log' % (path,filename), level=logging.INFO)
    logger.info(str)
    
while True:
    data = api.getTask()
    code = JsonHelper.get_int(data,'code')
    if code == 1:
        log('新任务')
        task_id = JsonHelper.get_string(data,'data','task_id')
        expire_time = JsonHelper.get_int(data,'data','expire_time')
        data = JsonHelper.get_value(data,'data','data')
        log('TASK_ID：%s' % task_id)
        log("任务内容：%s" % data)
        time.sleep(3)
        ret = api.setReport(task_id,'执行完成');
        log("完成上传：%s" % ret)

    time.sleep(1)

    