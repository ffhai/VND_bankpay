<?php


namespace app\common\utils;


/**
 * 时间工具
 */
class TimeUtils
{
    /**
     * 返回今日开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function today(): array
    {
        list($y, $m, $d) = explode('-', date('Y-m-d'));
        return [
            mktime(0, 0, 0, $m, $d, $y),
            mktime(23, 59, 59, $m, $d, $y)
        ];
    }

    /**
     * 返回明天开始和结束时间戳
     *
     * @author zero
     * @return array
     */
    public static function tomorrow(): array
    {
        $date = date("Y-m-d",strtotime("+1 day"));
        return [
            strtotime($date.' 00:00:00'),
            strtotime($date.' 23:59:59')
        ];
    }

    /**
     * 返回昨日开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function yesterday(): array
    {
        $yesterday = date('d') - 1;
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
        ];
    }

    /**
     * 返回本周开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function week(): array
    {
        list($y, $m, $d, $w) = explode('-', date('Y-m-d-w'));
        if($w == 0) $w = 7; //修正周日的问题
        return [
            mktime(0, 0, 0, $m, $d - $w + 1, $y), mktime(23, 59, 59, $m, $d - $w + 7, $y)
        ];
    }

    /**
     * 返回上周开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function lastWeek(): array
    {
        $timestamp = time();
        return [
            strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
            strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

    /**
     * 返回本月开始和结束的时间戳
     *
     * @return array
     * @author zero
     */
    public static function month(): array
    {
        list($y, $m, $t) = explode('-', date('Y-m-t'));
        return [
            mktime(0, 0, 0, $m, 1, $y),
            mktime(23, 59, 59, $m, $t, $y)
        ];
    }

    /**
     * 返回上个月开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function lastMonth(): array
    {
        $y = date('Y');
        $m = date('m');
        $begin = mktime(0, 0, 0, $m - 1, 1, $y);
        $end = mktime(23, 59, 59, $m - 1, date('t', $begin), $y);

        return [$begin, $end];
    }

    /**
     * 返回今年开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function year(): array
    {
        $y = date('Y');
        return [
            mktime(0, 0, 0, 1, 1, $y),
            mktime(23, 59, 59, 12, 31, $y)
        ];
    }

    /**
     * 返回去年开始和结束的时间戳
     *
     * @author zero
     * @return array
     */
    public static function lastYear(): array
    {
        $year = date('Y') - 1;
        return [
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        ];
    }

    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @author zero
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow(int $day = 1, bool $now = true): array
    {
        $end = time();
        if (!$now) {
            list($foo, $end) = self::yesterday();
        }

        unset($foo);
        return [
            mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
            $end
        ];
    }

    /**
     * 返回几天前的时间戳
     *
     * @author zero
     * @param int $day
     * @return int
     */
    public static function daysAgo(int $day = 1): int
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @author zero
     * @param int $day
     * @return int
     */
    public static function daysAfter(int $day = 1): int
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }

    /**
     * 天数转换成秒数
     *
     * @author zero
     * @param int $day
     * @return int
     */
    #[Pure]
    public static function daysToSecond(int $day = 1): int
    {
        return intval($day * 86400);
    }

    /**
     * 周数转换成秒数
     *
     * @author zero
     * @param int $week
     * @return int
     */
    #[Pure]
    public static function weekToSecond(int $week = 1): int
    {
        return intval(self::daysToSecond() * 7 * $week);
    }

    /**
     * 最近N天的日期
     *
     * @author zero
     * @param int $day
     * @return array
     */
    public static function nearToDate(int $day = 7): array
    {
        $time = time();
        $date = [];
        for ($i=1; $i<=$day; $i++){
            $date[$i-1] = date('Y-m-d' ,strtotime( '+' . ($i - $day) .' days', $time));
        }
        return $date;
    }

    /**
     * 当前毫秒数
     *
     * @author zero
     * @return float
     */
    public static function millisecond(): float
    {
        list($mse, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($mse) + floatval($sec)) * 1000);
    }

    /**
     * 返回今天是周几
     *
     * @author zero
     * @return mixed
     */
    public static function dayWeek(): string
    {
        $week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        return $week[date('w')];
    }

    /**
     * 大写月份
     *
     * @author zero
     * @param int $month
     * @return string
     */
    public static function capMonth(int $month=0): string
    {
        $month = $month > 0 ? $month : intval(date('m'));

        $data = [
            '一月','二月','三月','四月', '五月','六月','七月','八月','九月','十月','十一月','十二月'
        ];
        return $data[$month - 1] ?? '未知月份';
    }

    /** 时间戳到文本
     * @param int $timestamp 时间戳
     * @param string $format Y-m-d H:i:s
     * @return string
     */
    public static function formatTime(int $timestamp, string $format = 'Y-m-d H:i:s',string $default = ''): string
    {
        if($timestamp == 0){
            return $default;
        }
        return date($format,$timestamp);
    }

}