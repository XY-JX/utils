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

    public static function randomCode($limit = 4)
    {
        return strtoupper(substr(md5(uniqid()), 8, $limit));
    }

    /**
     * 生成一个id
     * @return string
     */
    public static function orderNo()
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
    public static function location_range($lng, $lat, $distance = 2)
    {
        $earthRadius = 6378.137;//单位km
        $d_lng = 2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat)));
        $d_lng = rad2deg($d_lng);
        $d_lat = $distance / $earthRadius;
        $d_lat = rad2deg($d_lat);
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
    public static function getDistance($lng1, $lat1, $lng2, $lat2)
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
    public static function Distance($lng1, $lat1, $lng2, $lat2)
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
    public static function ArrToXml($arr)
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
    public static function XmlToArr($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 生成二维码
     * @param string $text 内容
     * @param false $filename 文件名
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     * @param false $saveandprint
     */
    public static function qrcode(string $text, $filename = false, $level = 'L', $size = 4, $margin = 1, $saveandprint = false)
    {
        return \xy_jx\Utils\bin\QRcode::png($text, $filename, $level, $size, $margin, $saveandprint);
    }

    /**
     * 生成base64二维码
     * @param string $text 内容
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     * @param false $saveandprint
     */
    public static function base64qrcode(string $text, $level = 'L', $size = 4, $margin = 1)
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
    public static function wechat_verify(array $data, string $secretKey)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign == self::wechat_sign($data, $secretKey) ? true : false;
    }

    /**
     * 微信签名
     * @param array $data 需要签名的数据
     * @param string $secretKey 私钥
     * @return string
     */
    public static function wechat_sign(array $data, string $secretKey)
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
    public static function luck_draw(array $array, string $chance = 'chance', $stock = false)
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
     * redis
     * @param array $options redis配置
     * @return \Predis\Client|\Redis
     */
    public static function redis(array $options = [])
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
     */
    public static function restrict(array $options, string $key, int $limit = 3, string $time = 's')
    {
        $Redis = new Redis($options);
        return $Redis::restrict($key, $limit, $time);
    }

    /**
     * 递归无限级分类
     * @param $data
     * @param int $pid 父id
     * @param string $children 子分组
     * @param string $pfield 父字段
     * @param string $id
     * @return array
     */
    public static function recursion($data, $pid = 0, $children = 'children', $pfield = 'pid', $id = 'id')
    {
        $arr = [];
        foreach ($data as $key => $val) {
            if ($val[$pfield] == $pid) {
                unset($data[$key]);
                $val[$children] = self::recursion($data, $val[$id], $children, $pfield, $id);
                $arr[] = $val;
            }
        }
        return $arr;
    }
}