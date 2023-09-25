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
    protected static $publicKey;
    protected static $privateKey;
    protected static $splitLength = 117;


    /**
     * Openssl constructor.
     *
     * @param  string  $publicKeyFile  证书地址
     * @param  string  $privateKeyFile  证书地址
     *
     * @throws Exception
     */
    public function __construct(
        string $publicKeyFile = __DIR__.'/../cert/pub.key',
        string $privateKeyFile = __DIR__.'/../cert/pri.key',
        $byte = 4096
    ) {
        try {
            self::$publicKey   = file_get_contents($publicKeyFile);
            self::$privateKey  = file_get_contents($privateKeyFile);
            $array             = [
                1024 => 117,//1024字节证书密钥  1024/8-11  1024/8
                2048 => 245,//2048字节证书密钥  2048/8-11  2048/8
                4096 => 501,//4096字节证书密钥  4096/8-11  4096/8
            ];
            self::$splitLength = $array[$byte] ?? self::$splitLength;
        } catch (Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }


    /**
     * 私钥加密
     *
     * @param  array  $data
     *
     * @return string
     */
    public static function encrypt(array $data): string
    {
        $crypto = [];
        foreach (str_split(json_encode($data), self::$splitLength) as $chunk) {
            openssl_private_encrypt(
                $chunk,
                $encrypted,
                self::$privateKey
            );//私钥加密
            $crypto[] = base64_encode($encrypted);
        }

        return url_safe_encode(implode('.', $crypto));
    }

    /**
     * 公钥解密
     *
     * @param  string  $encrypted
     *
     * @return array
     */
    public static function decrypt(string $encrypted): array
    {
        $crypto = '';
        foreach (explode('.', url_safe_decode($encrypted)) as $chunk) {
            openssl_public_decrypt(
                base64_decode($chunk),
                $decrypted,
                self::$publicKey
            );//私钥加密的内容通过公钥可用解密出来
            $crypto .= $decrypted;
        }

        return json_decode($crypto, true) ?? [];
    }
}