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
    /**
     * 加密算法，使用openssl_get_cipher_methods()函数获取可用的加密算法列表。
     *
     * @var string
     */
    private static $method = 'aes-256-xts';
    /**
     * 密钥key
     *
     * @var string
     */
    private static $key = '1dfc5ac960771fc943bdfa1ad5ebdfe7';
    /**
     * 额外密钥
     *
     * @var string
     */
    private static $iv = '58162f5cea907ce1';//请保证16位
    /**
     * options 是以下标记的按位或： OPENSSL_RAW_DATA 、 OPENSSL_ZERO_PADDING
     *
     * @var int
     */
    private static $options = OPENSSL_ZERO_PADDING;
    /**
     * 超长加密分割长度
     * @var int
     */
    private static $splitLength = 660;
    /**
     * 超长加密分割符号
     * @var string
     */
    private static $splitSymbol = '.';


    public function __construct(array $config = [])
    {
        foreach ($config as $key => $val) {
            if (property_exists($this, $key)) {
                self::${$key} = $val;
            }
        }
    }

    /**
     * 设置参数
     *
     * @param $name
     * @param $val
     *
     * @return bool
     */
    public static function set($name, $val): bool
    {
        if (property_exists(self::class, $name)) {
            self::${$name} = $val;

            return true;
        }

        return false;
    }

    /**
     * 获取配置参数
     *
     * @param $name
     *
     * @return string
     */
    public static function get($name): ?string
    {
        if (property_exists(self::class, $name)) {
            return self::${$name};
        }

        return null;
    }

    /**
     * 加密
     *
     * @param array $data
     * @param string $iv
     *
     * @return string
     */
    public static function encrypt(array $data, string $iv = ''): string
    {
        return url_safe_encode(
            openssl_encrypt(
                json_encode($data),
                self::$method,
                self::$key,
                self::$options,
                $iv ?: self::$iv
            )
        );
    }

    /**
     * 解密
     *
     * @param string $data
     * @param string $iv
     *
     * @return array
     */
    public static function decrypt(string $data, string $iv = ''): array
    {
        return json_decode(
            openssl_decrypt(
                url_safe_decode($data),
                self::$method,
                self::$key,
                self::$options,
                $iv ?: self::$iv
            ),
            true
        ) ?? [];
    }


    /**
     * 超长文本加密
     *
     * @param array $encryptedData
     * @param string $iv
     *
     * @return string
     */
    public static function longEncrypt(array $encryptedData, string $iv = ''): string
    {
        $result = [];
        foreach (str_split(json_encode($encryptedData), self::$splitLength) as $chunk) {
            $result[] = openssl_encrypt(
                $chunk,
                self::$method,
                self::$key,
                self::$options,
                $iv ?: self::$iv
            );
        }

        return url_safe_encode(implode(self::$splitSymbol, $result));
    }

    /**
     * 超长文本解密
     *
     * @param string $encryptedData
     * @param string $iv
     *
     * @return array
     */
    public static function longDecrypt(string $encryptedData, string $iv = ''): array
    {
        $result = '';
        foreach (explode(self::$splitSymbol, url_safe_decode($encryptedData)) as $chunk) {
            $result .= openssl_decrypt(
                $chunk,
                self::$method,
                self::$key,
                self::$options,
                $iv ?: self::$iv
            );
        }

        return json_decode($result, true) ?? [];
    }


    /**
     * 重置密钥 不建议使用
     *
     * @return false|int
     */
    public static function resetKey()
    {
        $f = './src/Encryption.php';
        $s = uniqid(mt_rand(100, 999));
        $fileGet = file_get_contents($f);
        $file = str_replace(self::$key, md5($s), $fileGet);
        $file = str_replace(self::$iv, $s, $file);

        return file_put_contents($f, $file);
    }
}