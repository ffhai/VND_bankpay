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

data = {
    'package_name':'com.msb.digibank.retail',
    'title':'',
    'text':'',
    'tickerText':'',
    'extrasText':'Tài khoản: 800xx6204 Số dư: 70,300 VND ND: 80000186204-6282IBT1i1WJWDGM-DCZ8P1HF',
    'extrasBigText':'',
}
ret = api.addPending(data)
print(ret)
data = {
    'package_name':'com.google.android.apps.messaging',
    'title':'',
    'text':'',
    'tickerText':'',
    'extrasText':'Tai khoan VCB Digibank cua Quy khach dang dang nhap tren trinh duyet Web MOI. Ma OTP xac thuc la 535928 hieu luc 1 phut. TUYET DOI KHONG DE LO MA OTP',
    'extrasBigText':'',
}

ret = api.addPending(data)
print(ret)