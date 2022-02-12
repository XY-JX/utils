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
    private static $config = [
        'method' => 'aes-256-xts',
        'key' => '6f1b1d693ec48c9fdda723018eeb73fa',
        'iv' => 'encrypt@decrypt@', //请保证16位
        'options' => OPENSSL_ZERO_PADDING
    ];

    public function __construct(array $config = [])
    {
        if (!empty($config))
            self::$config = array_merge(self::$config, $config);
    }

    /**
     * 解密
     * @param string $data
     * @param string $iv
     * @return array
     */
    public static function decrypt(string $data, string $iv = ''): array
    {
        return json_decode(openssl_decrypt($data, self::$config['method'], self::$config['key'], self::$config['options'], $iv ?: self::$config['iv']), true);
    }

    /***
     * 加密
     * @param array $data
     * @param string $iv
     * @return false|string
     */
    public static function encrypt(array $data, string $iv = '')
    {
        return openssl_encrypt(json_encode($data), self::$config['method'], self::$config['key'], self::$config['options'], $iv ?: self::$config['iv']);
    }

    /**
     * 解密
     * @param string $encryptedData
     * @param string $iv
     * @return array
     */
    public static function longDecrypt(string $encryptedData, string $iv = ''): array
    {
        $result = '';
        foreach (str_split($encryptedData, 880) as $chunk) {
            $result .= openssl_decrypt($chunk, self::$config['method'], self::$config['key'], self::$config['options'], $iv ?: self::$config['iv']);
        }
        return json_decode($result, true);
    }

    /**
     * 加密
     * @param array $encryptedData
     * @param string $iv
     * @return string
     */
    public static function longEncrypt(array $encryptedData, string $iv = ''): string
    {
        $result = '';
        foreach (str_split(json_encode($encryptedData), 660) as $chunk) {
            $result .= openssl_encrypt($chunk, self::$config['method'], self::$config['key'], self::$config['options'], $iv ?: self::$config['iv']); //第四参数OPENSSL_RAW_DATA输出原始数据
        }
        return $result;
    }
}