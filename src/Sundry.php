<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/10 14:36
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  一些杂乱的方法
// +----------------------------------------------------------------------

namespace xy_jx\Utils;

class Sundry
{

    public static function randomCode($limit = 4): string
    {
        return strtoupper(substr(md5(uniqid()), 8, $limit));
    }

    /**
     * 生成一个id
     * @return string
     */
    public static function orderNo(): string
    {
        return date('YmdHis') . mt_rand(10, 99) . mt_rand(10, 99);
    }

    /**
     * 据传入的经纬度，和距离范围，返回所在距离范围内的经纬度的取值范围
     * @param $lng
     * @param $lat
     * @param float $distance 单位：km
     * @return array
     */
    public static function locationRange($lng, $lat, $distance = 2): array
    {
        $earthRadius = 6378.137;//单位km
        $d_lng = rad2deg(2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat))));
        $d_lat = rad2deg($distance / $earthRadius);
        return array(
            'lat_start' => round($lat - $d_lat, 7),//纬度开始
            'lat_end' => round($lat + $d_lat, 7),//纬度结束
            'lng_start' => round($lng - $d_lng, 7),//纬度开始
            'lng_end' => round($lng + $d_lng, 7)//纬度结束
        );
    }

    /**
     * 根据经纬度返回距离
     * @param $lng1 经度
     * @param $lat1 纬度
     * @param $lng2 经度
     * @param $lat2 纬度
     * @return float 距离：m
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2): float
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

    /**
     *  根据经纬度返回距离
     * @param $lng1 经度
     * @param $lat1 纬度
     * @param $lng2 经度
     * @param $lat2 纬度
     * @return string 距离：km,m
     */
    public static function distance($lng1, $lat1, $lng2, $lat2): string
    {
        $m = self::getDistance($lng1, $lat1, $lng2, $lat2);
        if ($m > 1000) {
            return round($m / 1000, 1) . 'km';
        } else {
            return $m . 'm';
        }
    }

    /**
     * array转XML
     * @param $arr
     * @return string
     */
    public static function arrToXml($arr): string
    {
        if (!is_array($arr) || count($arr) == 0) return '';
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将XML转为array
     * @param $xml
     * @return mixed
     */
    public static function xmlToArr($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 生成二维码 （建议前端生成）
     * @param string $text 内容
     * @param $filename 文件名
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     * @param bool $saveAndPrint 保存并打印
     * @return void
     */
    public static function qrcode(string $text, $filename = false, string $level = 'L', int $size = 4, int $margin = 1, bool $saveAndPrint = false)
    {
        return \xy_jx\Utils\bin\QRcode::png($text, $filename, $level, $size, $margin, $saveAndPrint);
    }

    /**
     * 生成base64二维码 （建议前端生成）
     * @param string $text 内容
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     */
    public static function base64Qrcode(string $text, string $level = 'L', int $size = 4, int $margin = 1): string
    {
        ob_start();
        self::qrcode($text, false, $level, $size, $margin);
        $img = ob_get_contents();//获取缓冲区内容
        ob_end_clean();//清除缓冲区内容
        ob_flush();
        return 'data:image/png;base64,' . str_replace(["\r\n", "\r", "\n"], '', chunk_split(base64_encode($img)));//转base64      清除base64中的换行符
    }

    /**
     * 微信验证签名
     * @param array $data 需要验签的数据
     * @param string $secretKey 私钥
     * @return bool
     */
    public static function wechatVerify(array $data, string $secretKey): bool
    {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign == self::wechatSign($data, $secretKey);
    }

    /**
     * 微信签名
     * @param array $data 需要签名的数据
     * @param string $secretKey 私钥
     * @return string
     */
    public static function wechatSign(array $data, string $secretKey): string
    {
        ksort($data);
        $SignTemp = urldecode(http_build_query($data)) . '&key=' . $secretKey;
        return strtoupper(md5($SignTemp));
    }

    /**
     * 幸运抽签
     * @param array $array 根据概率ASC 排序的二维数组 （中将概率必须是正整数）
     * @param string $chance 概率字段
     * @param false|string $stock 库存字段 不考虑库存不传
     * @return array|mixed
     */
    public static function luckDraw(array $array, string $chance = 'chance', $stock = false)
    {
        $return = [];
        $sum = 0;
        foreach ($array as $val) {
            $sum += $val[$chance]; //总概率
        }
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

    /**
     * 使用Redis
     * @param array $options redis配置
     * @return \Redis
     * @throws \Exception
     */
    public static function redis(array $options = []): \Redis
    {

        $Redis = new Redis($options);
        return $Redis::handler();
    }

    /**
     * 访问限制
     * @param array $options redis配置
     * @param string $key ip|uid
     * @param int $limit 限制次数
     * @param string $time 时间范围 s m h d
     * @return bool
     * @throws \Exception
     */
    public static function restrict(array $options, string $key, int $limit = 3, string $time = 's'): bool
    {
        $Redis = new Redis($options);
        return $Redis::restrict($key, $limit, $time);
    }

    /**
     * 递归无限级分类
     * @param $data
     * @param int $value 父id初始值
     * @param string $child 子分组
     * @param string $pid 父字段
     * @param string $id 子字段
     * @return array
     */
    public static function recursion($data, int $value = 0, string $child = 'child', string $pid = 'pid', string $id = 'id'): array
    {
        $arr = [];
        foreach ($data as $key => $val) {
            if ($val[$pid] == $value) {
                unset($data[$key]);
                $val[$child] = self::recursion($data, $val[$id], $child, $pid, $id);
                $arr[] = $val;
            }
        }
        return $arr;
    }

    /**
     * 返回数组中指定多列
     * @param array $array 需要取出数组列的多维数组
     * @param array $keys 要取出的列名，如不传则返回所有列
     * @param null $index_key 作为返回数组的索引的列
     * @return array
     */
    public static function arrayColumns(array $array, array $keys = [], $index_key = null): array
    {
        $result = [];
        if (!$array) return $result;
        foreach ($array as $v) {
            // 指定返回列
            $item = [];
            if ($keys) {
                foreach ($keys as $key) {
                    $item[$key] = $v[$key];
                }
            } else {
                $item = $v;
            }
            // 指定索引列
            if ($index_key) {
                $result[$v[$index_key]] = $item;
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @param mixed $num 需要转换的值
     * @param int $current 当前进制
     * @param int $result 需要转成的进制（最大支持62）
     * @return false|int|string
     */
    public static function convert($num, int $current = 10, int $result = 32): bool|int|string
    {
        if ($current > 62 || $result > 62) return false;
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

    /**
     * 生成密钥
     * @param $config $config = [
     *                              'config' => '/opt/service/php7.3.9/extras/ssl/openssl.cnf', // 定位至你的openssl.cnf文件
     *                              'digest_alg' => 'SHA512', // openssl_get_md_methods() 的返回值是可以使用的加密方法列表
     *                              'private_key_bits' => 4096,//512,1024,2048,4096  （不能使用字符型）
     *                           ]
     * @param $public_key 'public_key.cer' 此参数如果有值生成文件,无值返回公钥
     * @param $private_key 'private_key.cer' 此参数如果有值生成文件,无值返回私钥
     * @return bool|string
     */
    public static function generateKey($config, &$public_key, &$private_key): bool|string
    {
        if (!$resource = openssl_pkey_new($config)) return '配置参数错误';
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
