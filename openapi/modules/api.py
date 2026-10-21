import hashlib
import os
import time
from pathlib import Path

from modules.httpHelper import HttpClient

ENV_FILE = Path(__file__).resolve().parents[1] / '.env'


def _load_env_file(path: Path) -> None:
    """极简 .env 解析（KEY=VALUE，# 注释），已存在的环境变量优先"""
    if not path.is_file():
        return
    for raw_line in path.read_text(encoding='utf-8').splitlines():
        line = raw_line.strip()
        if not line or line.startswith('#') or '=' not in line:
            continue
        key, value = line.split('=', 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def _required_env(name: str) -> str:
    value = os.environ.get(name, '').strip()
    if not value:
        raise RuntimeError(
            f'缺少环境变量 {name}，请复制 openapi/.env.example 为 openapi/.env 并填写'
        )
    return value


_load_env_file(ENV_FILE)


class Api:
    # 凭据统一来自环境变量 / openapi/.env，不再硬编码到源码
    BASE_URL = _required_env('OPENAPI_BASE_URL').rstrip('/')
    APPID = _required_env('OPENAPI_APPID')
    SECRET = _required_env('OPENAPI_SECRET')

    def request(self,url = '',isPost = False,post_data = {}):
        client = HttpClient(
            base_url = self.BASE_URL,
            default_timeout = 10
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
        params['appid'] = self.APPID
        params['time'] = str(int(time.time()))
        sorted_params = "&".join([f"{k}={v}" for k, v in sorted(params.items())])
        sorted_params += '&secret=' + self.SECRET
        params['sign'] = hashlib.md5(sorted_params.encode('utf-8')).hexdigest()
        return params


    def getTask(self):
        url = '/task/api/getTask'
        data = {
        }

        req = self.request(url,True,data)
        return req

    def setReport(self,task_id,data):
        url = '/task/api/setReport'
        data = {
            'task_id':task_id,
            'data':data
        }
        req = self.request(url,True,data)
        return req