<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/9 19:43
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  加解密
// +----------------------------------------------------------------------

namespace xy_jx\Utils;

class Encryption
{

    private static $method = 'aes-256-xts';
    private static $key = 'dd2b1fa5a39b9051fb1982';
    private static $iv = 'encrypt@decrypt@';
    private static $options = OPENSSL_ZERO_PADDING;
    public function __construct(array $config = [])
    {
        self::$method = $config['method'] ?? 'aes-256-xts';
        self::$key = $config['key'] ?? 'dd2b1fa5a39b9051fb1982';
        self::$iv = $config['iv'] ?? 'encrypt@decrypt@';//请保证16位
        self::$options = $config['options'] ?? OPENSSL_ZERO_PADDING;
    }

    /**
     * 解密
     * @param string $data
     * @param string $iv
     * @return array
     */
    public static function decrypt(string $data, string $iv = '')
    {
        return json_decode(openssl_decrypt($data, self::$method, self::$key, self::$options, $iv ?: self::$iv), true);
    }

    /***
     * 加密
     * @param array $data
     * @param string $iv
     * @return false|string
     */
    public static function encrypt(array $data, string $iv = '')
    {
        return openssl_encrypt(json_encode($data), self::$method, self::$key, self::$options, $iv ?: self::$iv);
    }

    /**
     * 解密
     * @param string $encryptedData
     * @param string $iv
     * @return array
     */
    public static function long_decrypt(string $encryptedData, string $iv = '')
    {
        $result = '';
        foreach (str_split($encryptedData, 880) as $chunk) {
            $result .= openssl_decrypt($chunk, self::$method, self::$key, self::$options, $iv ?: self::$iv);
        }
        return json_decode($result, true);
    }

    /**
     * 加密
     * @param array $encryptedData
     * @param string $iv
     * @return array
     */
    public static function long_encrypt(array $encryptedData, string $iv = '')
    {
        $result = '';
        foreach (str_split(json_encode($encryptedData), 660) as $chunk) {
            $result .= openssl_encrypt($chunk, self::$method, self::$key, self::$options, $iv ?: self::$iv); //第四参数OPENSSL_RAW_DATA输出原始数据
        }
        return $result;
    }
}