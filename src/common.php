<?php
// +----------------------------------------------------------------------
// | DATE: 2023/09/20 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  一些函数方法
// +----------------------------------------------------------------------
if (!function_exists('random_code')) {
    /**
     * 随机码
     * @param int $limit
     * @return string
     */
    function random_code(int $limit = 4): string
    {
        return strtoupper(substr(md5(uniqid('', true)), 8, $limit));
    }
}
if (!function_exists('order_no')) {
    /**
     * 生成一个订单号
     * @return string
     */
    function order_no(): string
    {
        return date('YmdHis') . mt_rand(10, 99) . mt_rand(1000, 9999);
    }
}
if (!function_exists('rand_string')) {
    /**
     * 随机字符串
     * @param int $length 长度
     * @param string $seeds
     * @return void
     */
    function rand_string(int $length = 4, string $seeds = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY'): string
    {
        $seeds_all = str_split($seeds);
        $value = '';
        for ($i = 0; $i < $length; $i++) {
            $value .= $seeds_all[array_rand($seeds_all)];
        }
        return $value;
    }
}
if (!function_exists('generate_UUID')) {
    /**
     * 生成UUID
     * @return string
     */
    function generate_UUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
if (!function_exists('UUID')) {
    /**
     * 生成UUID
     * @return string
     */
    function UUID(): string
    {
        try {
            $data = random_bytes(16);
        } catch (Exception $e) {
            return generate_UUID();
        }
        // 设置 UUID 版本号（4 代），并将 UUID 变体设置为 DCE 1.1
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        // 格式化 UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
if (!function_exists('day_surplus_time')) {
    /**
     * 获取一天剩余时间秒
     * @return int
     */
    function day_surplus_time(): int
    {
        // 获取明天的 0 点（即明天的第一秒）的 UNIX 时间戳
        $endTime = strtotime('tomorrow') - 1;
        // 计算剩余时间的秒数
        $secondsLeft = $endTime - time();
        return max($secondsLeft, 1);
    }
}
if (!function_exists('url_safe_encode')) {
    /**
     * url安全编码
     * @param $string
     * @return string
     */
    function url_safe_encode($string): string
    {
        return strtr($string, '+/', '-_');
    }
}
if (!function_exists('url_safe_decode')) {
    /**
     * url安全解码
     * @param $string
     * @return string
     */
    function url_safe_decode($string): string
    {
        return strtr($string, '-_', '+/');
    }
}
