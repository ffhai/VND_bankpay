<?php

declare (strict_types = 1);

namespace app\common\service;

use app\common\enums\StatusEnum;
use app\common\exception\NotAuthException;
use app\common\exception\RequestException;
use app\common\model\ConfigModel;
use app\common\model\LoginLogModel;
use app\common\model\UserModel;
use app\common\utils\UrlUtils;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Cache;

/**
 * 登录服务类
 */
class LoginService
{
    /**
     * 密码错误计数的统计窗口（秒）
     */
    private const LOGIN_FAIL_TTL = 900;

    /**
     * 账号临时锁定时长（秒）
     */
    private const LOGIN_LOCK_TTL = 900;

    /**
     * 允许的最大密码错误次数
     */
    private const LOGIN_FAIL_MAX = 5;

    protected static function getJwtKey(){
        $key = (string) config('jwt.secret', '');
        if (!empty($key)) {
            return $key;
        }

        // 未配置 JWT_SECRET 时，自动生成密钥并持久化到「根」runtime 目录。
        // 注意：多应用模式下 runtime_path() 会按应用分区（runtime/common、runtime/admin…），
        // 必须使用根 runtime 目录，保证登录（common）与鉴权（admin）共享同一密钥
        $keyFile = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'jwt_key';
        if (is_file($keyFile)) {
            $key = trim((string) file_get_contents($keyFile));
        }
        if (empty($key)) {
            $key = md5(get_rand_str(32) . get_rand_str(32));
            file_put_contents($keyFile, $key, LOCK_EX);
        }
        return $key;
    }

    /*
     * 创建授权信息
     */
    public static function buildAuthorization($data = []){
        $payload = array(
            "iss" => UrlUtils::getDomain(), // 发行人
            "aud" => UrlUtils::getDomain(), // 接收者
            "iat" => time(), // 发行时间
            "nbf" => time(), // 生效时间
            "exp" => time() + (int) config('jwt.expire', 3600 * 24 * 7), // 过期时间
            "data" => $data
        );
        $jwt = JWT::encode($payload, self::getJwtKey(), 'HS256');
        return $jwt;
    }

    /*
     * 验证授权信息 返回用户信息
     */
    public static function checkAuthorization($jwt_str = ''){
        if(empty($jwt_str)){
            throw new NotAuthException('登陆已失效，请重新登录！');
        }
        if(intval(Cache::get('jwt_token_die_' . md5($jwt_str))) > 0){
            throw new NotAuthException('登陆已失效，请重新登录！');
        }
        try {
            $decoded = JWT::decode($jwt_str, new Key(self::getJwtKey(), 'HS256'));
        }catch (\Exception $e){
            throw new NotAuthException('登陆已失效，请重新登录！');
        }
        // 修改密码后，早于修改时间签发的 token 一律失效
        if (!self::isTokenActive($decoded)) {
            throw new NotAuthException('密码已修改，请重新登录！');
        }

        return $decoded->data; // 获取用户信息
    }

    /**
     * token 是否仍然有效（未被「修改密码」作废）
     *
     * @param object $decoded JWT 载荷
     */
    private static function isTokenActive(object $decoded): bool
    {
        $uid = (int) ($decoded->data->uid ?? 0);
        if ($uid <= 0) {
            return true;
        }

        // 比较「签发时的密码版本」与当前值是否一致：
        // 不用 iat 做大小比较，避免秒级精度导致同秒内的新旧 token 无法区分
        return (int) ($decoded->data->pwd_at ?? 0) === self::pwdMarker($uid);
    }

    /**
     * 当前密码版本（每次改密会更新，未改过密为 0）
     */
    private static function pwdMarker(int $uid): int
    {
        return (int) Cache::get(self::pwdResetKey($uid));
    }

    public static function login($params = []){
        $loginLogModel = new LoginLogModel();
        $username = (string) ($params['username'] ?? '');

        // 限时锁定：只锁定一段时间，避免被恶意试错把账号永久停用
        if (Cache::get(self::lockKey($username))) {
            throw new RequestException('密码错误次数过多，账号已锁定 ' . self::lockMinutes() . ' 分钟，请稍后再试！');
        }

        $userModel = new UserModel();
        $userItem = $userModel->where('username','=',$username)->field($userModel->fields)->find();
        if(!$userItem || $userItem['status'] != StatusEnum::STATUS_1){
            throw new RequestException('账号不存在或已停用！');
        }

        if(isset($params['password']) && !verify_password($params['password'], $userItem['password'])){
            // 计数带过期时间，过期即自动清零
            $loginFailCount = intval(Cache::get(self::failKey($username))) + 1;
            Cache::set(self::failKey($username), $loginFailCount, self::LOGIN_FAIL_TTL);
            $loginLogModel->log($userItem['id'],'',StatusEnum::STATUS_OFF,request()->ip());

            if($loginFailCount >= self::LOGIN_FAIL_MAX){
                Cache::delete(self::failKey($username));
                Cache::set(self::lockKey($username), time(), self::LOGIN_LOCK_TTL);

                throw new RequestException('密码重试次数过多，账号已锁定 ' . self::lockMinutes() . ' 分钟，请稍后再试！');
            }

            throw new RequestException('密码不正确，您还可以尝试['.(self::LOGIN_FAIL_MAX - $loginFailCount).']次!');
        }

        // 登录成功，清空失败计数与锁定
        self::clearLoginFail($username);

        $loginLogModel->log($userItem['id'],'',StatusEnum::STATUS_1,request()->ip());

        //系统停用 限制登录
        if($userItem['type'] != UserModel::TYPE_SUPER){
            $config = new ConfigModel();
            $site_status = intval($config->getContentByKey('site_status'));
            if($site_status != StatusEnum::STATUS_1){
                $site_stop_msg = $config->getContentByKey('site_stop_msg');
                throw new RequestException($site_stop_msg);
            }
        }

        $data = [
            'uid'=>$userItem['id'],
            'username'=>$userItem['username'],
            'type'=>$userItem['type'],
            // 记录签发时的密码版本，改密后该值与服务端不一致即视为失效
            'pwd_at'=>self::pwdMarker((int) $userItem['id']),
        ];
        return self::buildAuthorization($data);
    }


    public static function logout($jwt_token){
        if(empty($jwt_token)){
            return;
        }
        // 黑名单有效期必须覆盖 token 自身剩余有效期，
        // 否则固定 24h 的黑名单过期后，仍在 7 天有效期内的旧 token 会「复活」
        Cache::set('jwt_token_die_' . md5($jwt_token), time(), self::tokenRemainingTtl((string) $jwt_token));
    }

    /**
     * 作废某账号当前的登录态（修改密码后调用）
     *
     * 以「作废时间」为界：签发时间早于该时间的 token 全部失效
     */
    public static function invalidateUserTokens(int $uid): void
    {
        if ($uid <= 0) {
            return;
        }
        Cache::set(self::pwdResetKey($uid), time(), (int) config('jwt.expire', 3600 * 24 * 7));
    }

    /**
     * 清空账号的密码错误计数与限时锁定（登录成功或管理员重置密码后调用）
     */
    public static function clearLoginFail(string $username): void
    {
        if ($username === '') {
            return;
        }
        Cache::delete(self::failKey($username));
        Cache::delete(self::lockKey($username));
    }

    private static function failKey(string $username): string
    {
        return 'login_fail_' . $username;
    }

    private static function lockKey(string $username): string
    {
        return 'login_lock_' . $username;
    }

    private static function pwdResetKey(int $uid): string
    {
        return 'jwt_pwd_reset_' . $uid;
    }

    private static function lockMinutes(): int
    {
        return (int) ceil(self::LOGIN_LOCK_TTL / 60);
    }

    /**
     * token 剩余有效期，解析失败时退回配置的有效期
     */
    private static function tokenRemainingTtl(string $jwt): int
    {
        $payload = self::parsePayload($jwt);
        $exp = (int) ($payload['exp'] ?? 0);

        return $exp > time() ? $exp - time() : (int) config('jwt.expire', 3600 * 24 * 7);
    }

    /**
     * 解析 JWT 载荷（不校验签名，仅用于计算黑名单有效期）
     */
    private static function parsePayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return [];
        }
        $json = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($json === false) {
            return [];
        }
        $payload = json_decode($json, true);

        return is_array($payload) ? $payload : [];
    }
}