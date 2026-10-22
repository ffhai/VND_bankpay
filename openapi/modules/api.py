from modules.httpHelper import HttpClient
from modules.jsonHelper import JsonHelper
import hashlib
import time
import urllib3
import json

urllib3.disable_warnings()

class Api:
    base_url = ""
    appid = ''
    secret = ''

    def __init__(self,base_url,appid,secret):
        self.base_url = base_url
        self.appid = appid
        self.secret = secret
        pass

    def request(self,url = '',isPost = False,post_data = {}):
        client = HttpClient(
            base_url = self.base_url,
            default_timeout = 10,
            verify_ssl = False
        )
        headers = {}
        post_data = self.sign(post_data)
        if isPost:
            response = client.post(url,data=post_data,headers=headers)
        else:
            response = client.get(url)
        if response.is_success:
            return response.response.json()
        else:
            print(f"请求失败: {response.error_message}")
            return None

    def sign(self,params:dict):
        params['appid'] = self.appid
        params['time'] = str(int(time.time()))
        sorted_params = "&".join([f"{k}={v}" for k, v in sorted(params.items()) if v not in (None, "",0)])
        sorted_params += '&secret=' + self.secret
        params['sign'] = hashlib.md5(sorted_params.encode('utf-8')).hexdigest()
        return params


    def getNotifyTask(self):
        url = '/task/Api/getNotifyTask'
        data = {
        }
        req = self.request(url,True,data)
        return req

    def setNotifyStatus(self,order_id,status):
        url = '/task/Api/setNotifyStatus'
        data = {
            'order_id':order_id,
            'status':status
        }
        req = self.request(url,True,data)
        return req


    def getPending(self):
        url = '/task/Notify/getPending'
        data = {
        }
        req = self.request(url,True,data)
        return req

    def setPending(self,data):
        url = '/task/Notify/setPending'
        req = self.request(url,True,data)
        return req

    def addSmsTrade(self,data):
        url = '/task/Notify/addSmsTrade'
        req = self.request(url,True,dict(data))
        return req

    def addSmsCode(self,data):
        return self.request('/task/Notify/addSmsCode',True,dict(data))
    
    def addPending(self,data):
        url = '/task/Notify/add'
        req = self.request(url,True,data)
        return req
