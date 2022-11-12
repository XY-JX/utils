<?php
// +----------------------------------------------------------------------
// | DATE: 2022/11/11 16:43
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  生成一个唯一编码
// +----------------------------------------------------------------------
namespace xy_jx\Utils;

class Generate
{
    const EPOCH = 1668221577000;
    const max12bit = 4095;
    const max41bit = 1099511627775;

    static $machineId = null;

    /**
     * 设置机器id
     * @param int $mId
     * @return void
     */
    public static function machineId(int $mId = 0)
    {
        self::$machineId = $mId;
    }

    /**
     * 获取唯一编码
     * @return float|int
     */
    public static function getCode()
    {

        // Time - 42 bits

        $time = floor(microtime(true) * 1000);

        //从当前时间减去自定义纪元
        $time -= self::EPOCH;

        //创建一个基础，并添加时间
        $base = decbin(self::max41bit + $time);

        //配置的机器id - 10位-最多1024台机器
        if (self::$machineId) {
            $machineId = str_pad(decbin(self::$machineId), 10, "0", STR_PAD_LEFT);
        } else {
            $machineId = self::$machineId;
        }

        //序列号- 12位-每台机器最多4096个随机数
        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);

        $base = $base . $machineId . $random;

        //返回唯一的时间id no
        return bindec($base);
    }

    /**
     * 通过唯一编码获得时间戳
     * @param $particle
     * @return float|int
     */
    public static function codeToTime($particle)
    {
        //返回时间
        return bindec(substr(decbin($particle), 0, 41)) - self::max41bit + self::EPOCH;
    }

}