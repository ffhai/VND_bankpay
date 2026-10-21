<?php
// 应用公共文件
/**
 * 生成密码哈希（使用 PHP 推荐的默认算法，目前为 bcrypt）
 * @param string $password
 * @return string
 */
function make_password($password){
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * 校验密码
 * @param string $password 明文密码
 * @param string $hash     数据库中的密码哈希
 * @return bool
 */
function verify_password($password, $hash){
    return $hash !== '' && password_verify($password, $hash);
}

/**
 * 生成随机字符串
 * @param $length
 * @return string
 */
function get_rand_str($length){
    //字符组合
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len = strlen($str)-1;
    $randstr = '';
    for ($i = 0; $i < $length; $i++) {
        $num = mt_rand(0, $len);
        $randstr .= $str[$num];
    }
    return $randstr;
}

/**
 * 生成订单号
 * @return string
 */
function create_order_id(){
    $osn = date('dHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    $osn=strtoupper(get_rand_str(2)).$osn;
    return $osn;
}

/**
 * 发起GET请求
 *
 * @param string $url (请求链接)
 * @return mixed
 * @author zero
 */
function curl_get(string $url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    $data = curl_exec($curl);
    if (curl_error($curl)) {
        return false;
    } else {
        curl_close($curl);
        return $data;
    }
}

/**
 * 发起POST请求
 *
 * @param string $url (请求链接)
 * @param array $data (请求数据)
 * @param array $options (其它参数)
 * @return mixed
 * @author zero
 */
function curl_post(string $url, array $data = [], array $options=[]): mixed
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $headers[] = 'Content-Type: application/json; charset=utf-8';
    $headers = array_merge($headers, $options['headers']??[]);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($curl);
    if (curl_error($curl)) {
        print "Error: " . curl_error($curl);
        return false;
    } else {
        curl_close($curl);
        try {
            return json_decode($data, true);
        } catch (Exception $e) {
            return $data;
        }
    }
}
/**
 * 将字节转换为可读文本
 *
 * @param int $size (大小)
 * @param string $delimiter (分隔符)
 * @param int $precision (小数位数)
 * @return string
 * @author zero
 */
#[Pure]
function format_bytes(int $size, string $delimiter = '', int $precision = 2): string
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 6; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . $delimiter . $units[$i];
}

/*
 * 判断方法是否存在
 */
function check_action_exists($module, $controller, $action)
{
    try {
        $controllerClass = 'app\\' . $module . '\\controller\\' . $controller;
        if (!class_exists($controllerClass)) {
            return false;
        }

        $reflection = new \ReflectionClass($controllerClass);
        if ($reflection->getShortName() !== $controller || !$reflection->hasMethod($action)) {
            return false;
        }

        return $reflection->getMethod($action)->getName() === $action;
    } catch (\Throwable $e) {
        return false;
    }
}

/**
 * 获取客户端真实IP地址
 * 兼容负载均衡、CDN、反向代理等场景
 * 
 * @return string 客户端IP地址
 */
function getClientIp() {
    $ip = '0.0.0.0';
    
    // 代理服务器传递的真实IP头（按优先级排序）
    $ipHeaders = [
        'HTTP_X_FORWARDED_FOR',  // 标准代理头（Squid、Nginx、Apache等）
        'HTTP_X_REAL_IP',         // Nginx常用
        'HTTP_CF_CONNECTING_IP',  // Cloudflare
        'HTTP_TRUE_CLIENT_IP',    // 部分CDN
        'HTTP_X_CLUSTER_CLIENT_IP', // 集群环境
        'HTTP_X_FORWARDED',       // 某些代理
        'HTTP_FORWARDED_FOR',     // 某些代理
        'HTTP_FORWARDED'          // 某些代理
    ];
    
    // 遍历检查各个代理头
    foreach ($ipHeaders as $header) {
        if (isset($_SERVER[$header])) {
            $ipList = trim($_SERVER[$header]);
            if (!empty($ipList)) {
                // HTTP_X_FORWARDED_FOR 可能包含多个IP（逗号分隔）
                // 格式：client, proxy1, proxy2
                // 第一个IP是客户端真实IP
                $ips = explode(',', $ipList);
                $ip = trim($ips[0]);
                
                // 验证IP有效性
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }
    
    // 如果没有代理头，使用REMOTE_ADDR（直连IP）
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    
    // 处理CLI模式或特殊环境
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    
    return $ip;
}
