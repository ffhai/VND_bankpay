<?php
use GuzzleHttp\Client;
class Vietqr{
    protected $config = [
        'client_id'=>'',
        'api_key'=>'',
        'template'=>'ZnKTeUs',
    ];

    protected $client;
    public function __construct()
    {
        $this->config['client_id'] = env('vietqr.client_id', '');
        $this->config['api_key'] = env('vietqr.api_key', '');
        $this->client = new Client([
            'base_uri' => 'https://api.vietqr.io/v2/',
            //'http_errors' => false,
            'headers' => [
                'x-client-id' => $this->config['client_id'],
                'x-api-key' => $this->config['api_key'],
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function generate(array $params){
        $data = [
            'accountNo'=>$params['accountNo'],//收款人银行账号。仅输入数字，最少 6 位，最多 19 位
            'accountName'=>$params['accountName'],//银行账户名称。请用越南语输入，不含重音符号，全部大写
            'acqId'=>$params['acqId'],//银行识别号码（通常称为 BIN）是由国家银行监管的 6 位代码。
            'addInfo'=>$params['addInfo'],//转账详情。请输入越南语文本，不含重音符号，最多 25 个字符。请勿输入特殊字符。
            'amount'=>$params['amount'],//转账金额。仅输入数字，最多 13 个字符。
            'template'=>$this->config['template'],
        ];
        $response = $this->client->request('POST','generate',['json'=>$data]);
        $body = (string)$response->getBody();

        try {
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(json_last_error_msg());
            }
            return $data;
        } catch (RuntimeException $e) {
            // handle error
            \think\facade\Log::error('Vietqr generate error:'.$e->getMessage());
            return false;
        }

    }

    public function banks(){
        $response = $this->client->request('GET','banks');
        $body = (string)$response->getBody();
        return $body;
    }

}