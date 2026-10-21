<?php

declare (strict_types = 1);

namespace app\common\basics;

/**
 * 服务类基类
 *
 * 统一构造列表查询条件（setSearch），并提供 CRUD 方法占位。
 * setSearch 以「参数名@字段名」描述参数到字段的映射，直接返回条件数组，
 * 不再写入类级静态属性，避免多个 service 依次调用时互相污染。
 */
class Service
{
    /**
     * 直接比较的运算符类型
     */
    private const COMPARE_TYPES = ['=', '<>', '>', '>=', '<', '<=', 'in'];

    /**
     * 模糊匹配类型 => [前缀 %, 后缀 %]
     */
    private const LIKE_TYPES = [
        '%like%' => [true, true],
        '%like' => [true, false],
        'like%' => [false, true],
    ];

    /**
     * 构造搜索条件
     * PS: 参数名@字段名: user@U.sn
     *
     * @param array $search 规则数组：规则类型 => 「参数名@字段名」列表
     * @param array $params 请求参数
     * @return array 查询条件（可直接传给 where()）
     * @author zero
     */
    protected static function setSearch(array $search, array $params): array
    {
        $where = [];

        foreach ($search as $whereType => $whereFields) {
            foreach ($whereFields as $whereField) {
                if (in_array($whereType, self::COMPARE_TYPES, true)) {
                    [$key, $field] = self::parseField($whereField);
                    if (self::hasValue($params, $key)) {
                        $where[] = [$field, $whereType, $params[$key]];
                    }
                } elseif (isset(self::LIKE_TYPES[$whereType])) {
                    [$key, $field] = self::parseField($whereField);
                    if (self::hasValue($params, $key)) {
                        $where[] = [$field, 'like', self::likePattern($whereType, (string) $params[$key])];
                    }
                } elseif ($whereType === 'datetime') {
                    foreach (self::datetimeWhere($whereField, $params) as $item) {
                        $where[] = $item;
                    }
                } elseif ($whereType === 'between') {
                    $item = self::betweenWhere($whereField, $params);
                    if ($item !== null) {
                        $where[] = $item;
                    }
                } elseif ($whereType === 'keyword') {
                    $item = self::keywordWhere($whereFields, $params);
                    if ($item !== null) {
                        $where[] = $item;
                    }
                }
            }
        }

        return $where;
    }

    /**
     * 解析「参数名@字段名」，未写 @ 时参数名与字段名相同
     *
     * @return array [参数名, 字段名]
     */
    private static function parseField(string $whereField): array
    {
        if (strpos($whereField, '@') === false) {
            return [$whereField, $whereField];
        }

        $parts = explode('@', $whereField);

        return [$parts[0], $parts[1] ?? $parts[0]];
    }

    /**
     * 参数是否有有效值（0 与 '0' 视为有值）
     */
    private static function hasValue(array $params, string $key): bool
    {
        return isset($params[$key]) && (is_numeric($params[$key]) || !empty($params[$key]));
    }

    /**
     * 按模糊匹配类型拼接 like 模式
     */
    private static function likePattern(string $whereType, string $value): string
    {
        [$prefix, $suffix] = self::LIKE_TYPES[$whereType];

        return ($prefix ? '%' : '') . $value . ($suffix ? '%' : '');
    }

    /**
     * 时间条件（支持单值起始时间与 [起, 止] 两种写法）
     *
     * @return array 条件项列表
     */
    private static function datetimeWhere(string $whereField, array $params): array
    {
        [$key, $field] = self::parseField($whereField);
        if (!isset($params[$key]) || empty($params[$key])) {
            return [];
        }

        if (!is_array($params[$key])) {
            $start = self::toTimestamp($params[$key]);

            return $start === null ? [] : [[$field, '>=', $start]];
        }

        $where = [];
        $start = self::toTimestamp($params[$key][0] ?? '');
        $end = self::toTimestamp($params[$key][1] ?? '');
        if ($start !== null) {
            $where[] = [$field, '>=', $start];
        }
        if ($end !== null) {
            $where[] = [$field, '<=', $end];
        }

        return $where;
    }

    /**
     * 区间条件，参数写法：起始参数|结束参数@字段名
     */
    private static function betweenWhere(string $whereField, array $params): ?array
    {
        [$keys, $field] = self::parseField($whereField);
        $range = explode('|', $keys);
        if (count($range) < 2 || empty($params[$range[0]]) || empty($params[$range[1]])) {
            return null;
        }

        return [$field, 'between', [$params[$range[0]], $params[$range[1]]]];
    }

    /**
     * 同值多字段搜索，规则形如：keyword_type => ['=', '字段名']
     */
    private static function keywordWhere(array $whereFields, array $params): ?array
    {
        $type = $params['keyword_type'] ?? '';
        $value = $params['keyword'] ?? '';
        if ($value === '' || !isset($whereFields[$type])) {
            return null;
        }

        [$operator, $field] = $whereFields[$type];
        if (in_array($operator, self::COMPARE_TYPES, true)) {
            return [$field, $operator, $value];
        }
        if (isset(self::LIKE_TYPES[$operator])) {
            return [$field, 'like', self::likePattern($operator, (string) $value)];
        }

        return null;
    }

    /**
     * 时间参数统一转为时间戳
     * 支持 10 位时间戳（前端 value-format="X" 输出）与日期字符串（2026-10-14 00:48:49）
     *
     * @param mixed $value
     * @return int|null 无法识别时返回 null
     * @author zero
     */
    protected static function toTimestamp($value): ?int
    {
        if (is_int($value) || (is_string($value) && preg_match('/^\d{10}$/', $value))) {
            return (int) $value;
        }

        $time = strtotime((string) $value);
        return $time === false ? null : $time;
    }

    /*
     * 通用 CRUD（子类按需覆盖）
     */
    public static function lists(array $params, array $userItem): array
    {
        return [];
    }

    public static function add(array $params, array $userItem)
    {
    }

    public static function edit(array $params, array $userItem): void
    {
    }

    public static function del(array $params, array $userItem): void
    {
    }

    /*
     * 通用 获取选项列表
     */
    public static function getOptions(array $userItem): array
    {
        return [];
    }
}
