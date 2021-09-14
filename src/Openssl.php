<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/9 19:43
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  证书加解密
// +----------------------------------------------------------------------

namespace xy_jx\Utils;

use Exception;

class Openssl
{
    protected static $public_key;
    protected static $private_key;
    protected static $level;

    /**
     * Openssl constructor.
     * @param string $publicKeyFile 证书地址
     * @param string $privateKeyFile 证书地址
     * @param int $level 钥位数
     */
    public function __construct(string $publicKeyFile = __DIR__ . '/cert/pub.key', string $privateKeyFile = __DIR__ . '/cert/pri.key', int $level = 4096)
    {
        try {
            self::$public_key = file_get_contents($publicKeyFile);
            self::$private_key = file_get_contents($privateKeyFile);
            self::$level = $level;
        } catch (Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
//    1024   117   128
//    2048   245   256
//    4096   501   512

    protected static function level()
    {
        $array = [
            1024 => [117, 128],//1024字节证书密钥  1024/8-11  1024/8
            2048 => [245, 256],//2048字节证书密钥  2048/8-11  2048/8
            4096 => [501, 512],//4096字节证书密钥  4096/8-11  4096/8
        ];
        return $array[self::$level];
    }

    /**
     * 私钥加密
     * @param array $data
     * @param int $level
     * @return string
     */
    public static function encrypt(array $data)
    {
        $crypto = '';
        foreach (str_split(json_encode($data), self::level()[0]) as $chunk) {
            openssl_private_encrypt($chunk, $encrypted, self::$private_key);//私钥加密
            $crypto .= $encrypted;
        }
        return base64_encode($crypto);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @param int $level
     * @return mixed
     */
    public static function decrypt(string $encrypted)
    {
        $crypto = '';
        foreach (str_split(base64_decode($encrypted), self::level()[1]) as $chunk) {
            openssl_public_decrypt($chunk, $decrypted, self::$public_key);//私钥加密的内容通过公钥可用解密出来
            $crypto .= $decrypted;
        }
        return json_decode($crypto,true);
    }
}