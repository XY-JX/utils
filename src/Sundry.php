<?php
declare (strict_types=1);
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
     * 据传入的经纬度，和距离范围，返回所有在距离范围内的经纬度的取值范围
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
     * @return float 距离：km <=50km
     */
    public static function Distance($lng1, $lat1, $lng2, $lat2)
    {
        $m = self::getDistance($lng1, $lat1, $lng2, $lat2);
        if ($m > 1000) {
            if ($m > 50000) {
                return '';
            } else {
                return round(($m / 1000), 1) . 'km';
            }
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
     * @param $text 内容
     * @param false $filename 文件名
     * @param string $level 等级3 L M Q H
     * @param int $size 大小
     * @param int $margin 边框
     */
    public static function qrcode($text, $filename = false, $level = 'L', $size = 4, $margin = 1)
    {
        return \xy_jx\Utils\bin\QRcode::png($text, $filename, $level, $size, $margin);
    }
}