<?php
class Pay{

    private $URL = '';

    private $APPID = '';
    private $APPKEY = '';

    public function __construct($request,$appid, $appkey){
        $this->URL = trim($request);
        $this->APPID = trim($appid);
        $this->APPKEY = trim($appkey);
    }

    /*
     * 创建订单
     * {"code":1,"msg":"创建成功","data":{"order_id":"4DK2013373410152485","pay_url":"http:\/\/2.test.com\/openapi\/pay\/index?oid=4DK2013373410152485"}}
     */
    public function createOrder($params = []){
        $data = [];
        $data['appid'] = $this->APPID;
        $data['pay_type'] = $params['pay_type'];
        $data['bank_code'] = $params['bank_code'];
        $data['amount'] = intval($params['amount']);
        $data['trade_no'] = $params['trade_no'];
        $data['client_ip'] = isset($params['client_ip'])?$params['client_ip']:'';
        $data['device'] = isset($params['device'])?$params['device']:'';
        $data['notify_url'] = trim($params['notify_url']);
        $data['return_url'] = trim($params['return_url']);
        $data['time'] = time();
        $data['sign'] = $this->_sign($data,$this->APPKEY);

        $url = $this->URL.'/openapi/Order/create';
        return $this->_post($url,$data);
    }

    /*
     * 订单查询
     * {"code":1,"msg":"获取成功","data":{"order_id":"N91414505198565510","trade_no":"1786690251","pay_type":"alipay","amount":"200.00","payment":"200.00","status":1,"pay_time":0}}
     */
    public function queryOrder($order_id){
        $data = [];
        $data['appid'] = $this->APPID;
        $data['order_id'] = $order_id;
        $data['time'] = time();
        $data['sign'] = $this->_sign($data,$this->APPKEY);

        $url = $this->URL.'/openapi/Order/query';
        return $this->_post($url,$data);
    }
    /*
     * 银行列表
     */
    public function banks(){
        $data = [];
        $data['appid'] = $this->APPID;
        $data['time'] = time();
        $data['sign'] = $this->_sign($data,$this->APPKEY);

        $url = $this->URL.'/openapi/Order/banks';
        return $this->_post($url,$data);
    }

    private function _sign($params = [],$appkey=''){
        ksort($params);
        $data = '';
        foreach($params as $k=>$v){
            if($k != 'sign' && !empty($v)){
                if(empty($data)){
                    $data = $k.'='.$v;
                }else{
                    $data = $data.'&'.$k.'='.$v;
                }
            }
        }
        return md5($data.'&secret='.$appkey);
    }

    private function _post($url,$data){
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        return $return;
    }
}