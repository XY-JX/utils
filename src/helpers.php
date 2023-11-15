<?php
// +----------------------------------------------------------------------
// | DATE: 2023/09/20 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  一些函数方法
// +----------------------------------------------------------------------
if (!function_exists('rmb_capital')) {
    /**
     * 数字人民币转汉字大写
     * @param $amount
     * @return string
     */
    function rmb_capital($amount): string
    {
        return \xy_jx\Utils\Rmb::rmbCapital($amount);
    }
}
if (!function_exists('order_no')) {
    /**
     * 生成一个订单号
     *
     * @return string
     */
    function order_no(): string
    {
        return date('ymdHis') . mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(1000, 9999);
    }
}
if (!function_exists('rand_string')) {
    /**
     * 随机字符串
     *
     * @param int $length 长度
     * @param string $seeds
     *
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
     *
     * @return string
     */
    function generate_UUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
if (!function_exists('UUID')) {
    /**
     * 生成UUID
     *
     * @return string
     */
    function UUID(): string
    {
        try {
            $data = random_bytes(16);
        } catch (Exception $e) {
            $data = openssl_random_pseudo_bytes(16);
        }
        // 设置 UUID 版本号（4 代），并将 UUID 变体设置为 DCE 1.1
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // 格式化 UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
if (!function_exists('global_id')) {
    /**
     * 生成全局ID
     * @return string
     */
    function global_id(): string
    {
        try {
            $data = random_bytes(12);
        } catch (Exception $e) {
            $data = openssl_random_pseudo_bytes(12);
        }
        // 设置 UUID 版本号（4 代），并将 UUID 变体设置为 DCE 1.1
        $data[2] = chr(ord($data[2]) & 0x0f | 0x40);
        $data[4] = chr(ord($data[4]) & 0x3f | 0x80);

        // 格式化 UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(dechex(time()) . bin2hex($data), 4));
    }
}
if (!function_exists('day_surplus_time')) {
    /**
     * 获取一天剩余时间秒
     *
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
     *
     * @param $string
     *
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
     *
     * @param $string
     *
     * @return string
     */
    function url_safe_decode($string): string
    {
        return strtr($string, '-_', '+/');
    }
}
if (!function_exists('location_range')) {
    /**
     * 据传入的经纬度，和距离范围，返回所在距离范围内的经纬度的取值范围
     *
     * @param  $lng /经度
     * @param  $lat /纬度
     * @param float $distance 单位：km
     *
     * @return array
     */
    function location_range($lng, $lat, $distance = 2): array
    {
        $earthRadius = 6378.137;//单位km
        $d_lng = rad2deg(
            2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat)))
        );
        $d_lat = rad2deg($distance / $earthRadius);

        return array(
            'lat_start' => round($lat - $d_lat, 7),//纬度开始
            'lat_end' => round($lat + $d_lat, 7),//纬度结束
            'lng_start' => round($lng - $d_lng, 7),//纬度开始
            'lng_end' => round($lng + $d_lng, 7),//纬度结束
        );
    }
}
if (!function_exists('get_location_distance')) {
    /**
     * 根据经纬度返回距离
     *
     * @param $lng1 /经度
     * @param $lat1 /纬度
     * @param $lng2 /经度
     * @param $lat2 /纬度
     *
     * @return float 距离：m
     */
    function get_location_distance($lng1, $lat1, $lng2, $lat2): float
    {
        $radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6370996;

        return round($s, 0);
    }
}
if (!function_exists('get_location_distance_format')) {
    /**
     * 获取位置距离格式
     *
     * @param $lng1 /经度
     * @param $lat1 /纬度
     * @param $lng2 /经度
     * @param $lat2 /纬度
     *
     * @return string 距离：km,m
     */
    function get_location_distance_format($lng1, $lat1, $lng2, $lat2): string
    {
        $m = get_location_distance($lng1, $lat1, $lng2, $lat2);
        if ($m > 1000) {
            return round($m / 1000, 1) . 'km';
        } else {
            return $m . 'm';
        }
    }
}
if (!function_exists('array_to_xml')) {
    /**
     * array转XML
     *
     * @param array $array
     * @param string $root
     * @return string
     */
    function array_to_xml(array $array, string $root = 'xml'): string
    {
        if (count($array) == 0) {
            return '';
        }
        $xml = "<$root>";
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $xml .= array_to_xml($val, $key);
            } elseif (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</$root>";

        return $xml;
    }
}

if (!function_exists('xml_to_array')) {
    /**
     * 将XML转为array
     *
     * @param $xml
     *
     * @return mixed
     */
    function xml_to_array($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}
if (!function_exists('generate_qrcode')) {
    /**
     * 生成二维码 （建议前端生成）
     *
     * @param string $text 内容
     * @param mixed $filename /文件名
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     * @param bool $saveAndPrint 保存并打印
     */
    function generate_qrcode(string $text, $filename = false, string $level = 'L', int $size = 4, int $margin = 1, bool $saveAndPrint = false)
    {
        return \xy_jx\Utils\bin\QRcode::png($text, $filename, $level, $size, $margin, $saveAndPrint);
    }
}
if (!function_exists('generate_base64_qrcode')) {
    /**
     * 生成base64二维码 （建议前端生成）
     *
     * @param string $text 内容
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     */
    function generate_base64_qrcode(string $text, string $level = 'L', int $size = 4, int $margin = 1): string
    {
        ob_start();
        generate_qrcode($text, false, $level, $size, $margin);
        $img = ob_get_clean();//得到当前缓冲区的内容并删除当前输出缓冲区

        return 'data:image/png;base64,' . str_replace(["\r\n", "\r", "\n"], '', chunk_split(base64_encode($img)));//转base64      清除base64中的换行符
    }
}
if (!function_exists('luck_draw')) {
    /**
     * 幸运抽签
     *
     * @param array $array 根据概率ASC 排序的二维数组 （中将概率必须是正整数）
     * @param string $chance 概率字段
     * @param false|string $stock 库存字段 不考虑库存不传
     *
     * @return array|mixed
     */
    function luck_draw(array $array, string $chance = 'chance', $stock = false)
    {
        $return = [];
        $sum = array_sum(array_column($array, $chance));////总概率
        //概率数组循环
        foreach ($array as $val) {
            $randNum = mt_rand(1, $sum);
            if ($randNum <= $val[$chance] && (!$stock || $val[$stock] > 0)) { //如果这个随机数小于等于数组中的一个元素 并且库存足够，则返回中将数据
                $return = $val;
                break;
            } else {
                $sum -= $val[$chance];
            }
        }

        return $return;
    }
}
if (!function_exists('recursion')) {
    /**
     * 递归无限级分类
     *
     * @param array $data
     * @param int $value 父id初始值
     * @param string $child 子分组
     * @param string $pid 父字段
     * @param string $id 子字段
     *
     * @return array
     */
    function recursion(array $data, int $value = 0, string $child = 'child', string $pid = 'pid', string $id = 'id'): array
    {
        $arr = [];
        foreach ($data as $key => $val) {
            if ($val[$pid] == $value) {
                unset($data[$key]);
                $val[$child] = recursion($data, $val[$id], $child, $pid, $id);
                $arr[] = $val;
            }
        }

        return $arr;
    }
}
if (!function_exists('format_two_array')) {
    /**
     * 格式二维数组,类似array_column()可以指定多列
     * @param array $array 需要取出数组列的多维数组
     * @param array $keys 要取出的列名，如不传则返回所有列
     * @param mixed $index_key 作为返回数组的索引的列
     * @param mixed $default 默认值
     *
     * @return array
     */
    function format_two_array(array $array, array $keys = [], $index_key = null, $default = ''): array
    {
        $result = [];
        foreach ($array as $v) {
            $item = format_array($v, $keys, $default);
            if ($index_key && array_key_exists($index_key, $v)) {
                $result[$v[$index_key]] = $item;
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }
}
if (!function_exists('format_array')) {
    /**
     * 格式一维数组
     * @param array $array 需要格式的数组
     * @param array $keys 要取出的列名，如不传则返回所有列
     * @param string $default 默认值
     * @return array
     */
    function format_array(array $array, array $keys = [], string $default = ''): array
    {
        $item = $array;
        if (empty($keys)) {
            return $item;
        }
        $item = array_intersect_key($array, array_flip($keys));
        $item += array_fill_keys(array_diff($keys, array_keys($item)), $default);
        return $item;
    }
}
if (!function_exists('many_convert')) {
    /**
     * 多进制转换
     *
     * @param mixed $num 需要转换的值
     * @param int $current 当前进制
     * @param int $result 需要转成的进制（最大支持62）
     *
     * @return bool|int|string
     */
    function many_convert($num, int $current = 10, int $result = 32)
    {
        if ($current > 62 || $result > 62) {
            return false;
        }
        if ($current > 32 || $result > 32) {
            $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            if ($current > $result) { // 62进制数转换成十进制数
                $num = strval($num);
                $len = strlen($num);
                $dec = 0;
                for ($i = 0; $i < $len; $i++) {
                    $pos = strpos($dict, $num[$i]);
                    $dec = bcadd(bcmul(bcpow($current, $len - $i - 1), $pos), $dec);
                }

                return $dec;
            } else { //十进制数转换成62进制
                $ret = '';
                do {
                    $ret = $dict[bcmod($num, $result)] . $ret;
                    $num = bcdiv($num, $result);
                } while ($num > 0);

                return $ret;
            }
        }

        return base_convert($num, $current, $result);
    }
}
if (!function_exists('generate_key')) {
    /**
     * 生成密钥
     *
     * @param $config $config = [
     *                              'config' => '/opt/service/php7.3.9/extras/ssl/openssl.cnf', // 定位至你的openssl.cnf文件
     *                              'digest_alg' => 'SHA512', // openssl_get_md_methods() 的返回值是可以使用的加密方法列表
     *                              'private_key_bits' => 4096,//512,1024,2048,4096  （不能使用字符型）
     *                           ]
     * @param $public_key 'public_key.cer' 此参数如果有值生成文件,无值返回公钥
     * @param $private_key 'private_key.cer' 此参数如果有值生成文件,无值返回私钥
     *
     * @return bool
     */
    function generate_key($config, string &$public_key, string &$private_key): bool
    {
        if (!$resource = openssl_pkey_new($config)) {
            //配置参数错误
            return false;
        }
        // 生成私钥
        openssl_pkey_export($resource, $privateKey, null, $config);
        // 生成公钥
        $details = openssl_pkey_get_details($resource);
        //生成公钥文件
        if ($public_key) {
            $fp = fopen($public_key, "w");
            fwrite($fp, $details['key']);
            fclose($fp);
        } else {
            $public_key = $details['key'];
        }
        //生成密钥文件
        if ($private_key) {
            $fp = fopen($private_key, "w");
            fwrite($fp, $privateKey);
            fclose($fp);
        } else {
            $private_key = $privateKey;
        }

        return true;
    }
}

