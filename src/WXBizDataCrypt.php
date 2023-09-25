<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/20 14:36
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  对微信小程序用户加密数据的解密
// +----------------------------------------------------------------------

namespace xy_jx\Utils;

class WXBizDataCrypt
{
    private static $appid;
    private static $sessionKey;

    /**
     * 构造函数
     * WXBizDataCrypt constructor.
     *
     * @param $appid string 小程序的appid
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     */
    public function __construct(string $appid, string $sessionKey)
    {
        self::$sessionKey = $sessionKey;
        self::$appid      = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     *
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data  解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public static function decryptData(
        string $encryptedData,
        string $iv,
        &$data
    ): int {
        if (strlen(self::$sessionKey) != 24) {
            return -41001;
        }
        $aesKey = base64_decode(self::$sessionKey);


        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt(
            $aesCipher,
            "AES-128-CBC",
            $aesKey,
            1,
            $aesIV
        );

        $dataObj = json_decode($result, true);
        if ($dataObj == null) {
            return -41003;
        }
        if ($dataObj['watermark']['appid'] != self::$appid) {
            return -41003;
        }
        $data = $dataObj;

        return 0;
    }

}

