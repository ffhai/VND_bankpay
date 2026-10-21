<?php

declare (strict_types = 1);

namespace app\common\basics;

use app\common\enums\CodeEnum;
use think\exception\HttpResponseException;
use think\Response;

/**
 * 验证器基类
 */
class Validate extends \think\Validate
{
    /**
     * 切面验证接收到的参数
     *
     * @author zero
     * @param string $scene
     * @param array $data (扩展的验证参数)
     * @return void
     */
    public function goCheck(string $scene='', array $data=[]): void
    {
        $params = request()->param();
        if (!empty($data)) {
            $params = array_merge($params, $data);
        }

        if (!($scene ? $this->scene($scene)->check($params) : $this->check($params))) {
            $exception = is_array($this->error) ? implode(';', $this->error) : $this->error;
            $data = array('code'=>CodeEnum::ERROR, 'msg'=>$exception, 'data'=>[]);
            $response = Response::create($data, 'json', 422);
            throw new HttpResponseException($response);
        }
    }

    /**
     * 主键场景验证器
     *
     * @author zero
     * @param array $data
     * @return void
     */
    public function idCheck(array $data=[]): void
    {
        $this->only(['id']);
        $this->goCheck('', $data);
    }

    /**
     * 批量场景验证器
     *
     * @author zero
     * @param array $data
     * @return void
     */
    public function idsCheck(array $data=[]): void
    {
        $this->only(['ids']);
        $this->goCheck('', $data);
    }

    /**
     * 新增场景验证器
     *
     * @author zero
     * @param array $data
     * @return void
     */
    public function addCheck(array $data=[]): void
    {
        $this->remove('id', 'require');
        $this->remove('ids', 'require');
        $this->goCheck('', $data);
    }

    /**
     * 编辑场景验证器
     *
     * @param array $data
     * @author zero
     */
    public function editCheck(array $data=[]): void
    {
        $this->remove('ids', 'require');
        $this->goCheck('', $data);
    }

    /**
     * 验证正整数规则
     *
     * @param mixed $value (需验证的值)
     * @return bool
     * @author zero
     */
    #[Pure]
    protected function posInteger(mixed $value): bool
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }

    /**
     * 验证最小值规则
     *
     * @param mixed $value (需验证的值)
     * @param mixed $rule (规则值)
     * @return bool
     * @author zero
     */
    protected function minValue(mixed $value, mixed $rule): bool
    {
        if ($value < $rule) {
            return false;
        }

        return true;
    }

    /**
     * 验证最大值规则
     *
     * @param mixed $value (需验证的值)
     * @param mixed $rule (规则值)
     * @return bool
     * @author zero
     */
    protected function maxValue(mixed $value, mixed $rule): bool
    {
        if ($value > $rule) {
            return false;
        }

        return true;
    }
}