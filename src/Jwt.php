<?php
// +----------------------------------------------------------------------
// | DATE: 2023/09/22 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  JWT
// +----------------------------------------------------------------------
namespace xy_jx\Utils;

class Jwt
{
    /**
     * 签发者
     *
     * @var string
     */
    protected static $iss = 'xy-jx';
    /**
     * 接收者
     *
     * @var string
     */
    protected static $aud = 'xy-jx';
    /**
     * 签发类型
     *
     * @var string
     */
    protected static $type = 'Bearer';
    /**
     * 额外密钥必须16位字符(请自己设置并防止泄漏)
     *
     * @var string
     */
    protected static $iv = '';//请保证16位
    /**
     * 加密key
     * @var string
     */
    protected static $key = '';


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
     * 获取token
     *
     * @param array $user
     * @param array $auth
     * @param int $expire
     *
     * @return array|bool
     */
    public static function getToken(array $user, array $auth = [], int $expire = 0)
    {
        $time = time();
        $sigData = [
            'iss' => self::$iss,//签发者
            'aud' => self::$aud,//接收者
            'type' => self::$type,//类型
            'iat' => $time,//签发时间
            'exp' => $time + ($expire ?: 86400 * 7),//过期时间
            'user' => $user,//需要存储的用户信息
            'auth' => $auth,//需要存储的额外信息如授权
            'uuid' => global_id(),
        ];

        $data = [
            'type' => self::$type,
            'exp' => $sigData['exp'],
            'uuid' => $sigData['uuid'],
        ];
        self::setKey();
        if (!$token = Encryption::encrypt($sigData, self::$iv)) {
            return false;
        }
        //加密获取token
        $data['token'] = $token;

        return $data;
    }

    /**
     * 获取用户信息
     *
     * @param string $token
     * @param int $iat
     * @param null $uuid
     * @param array $auth
     *
     * @return array
     */
    public static function getUser(string $token, int $iat = 0, &$uuid = null, array &$auth = []): array
    {
        $tokenData = self::checkToken($token, $iat);
        $uuid = $tokenData['uuid'] ?? null;
        $auth = $tokenData['auth'] ?? [];

        return $tokenData['user'] ?? [];
    }

    /**
     * 获取uuid
     *
     * @param string $token
     * @param int $iat
     * @return mixed|null
     */
    public static function getUUID(string $token, int $iat = 0)
    {
        $tokenData = self::checkToken($token, $iat);

        return $tokenData['uuid'] ?? null;
    }

    /**
     * 获取type
     *
     * @param string $token
     * @param int $iat
     * @return mixed|null
     */
    public static function getType(string $token, int $iat = 0)
    {
        $tokenData = self::checkToken($token, $iat);

        return $tokenData['type'] ?? null;
    }

    /**
     * 获取到期时间戳
     *
     * @param string $token
     * @param int $iat
     * @return int
     */
    public static function getExp(string $token, int $iat = 0): int
    {
        $tokenData = self::checkToken($token, $iat);

        return $tokenData['exp'] ?? 0;
    }

    /**
     * 获取授权信息
     *
     * @param string $token
     * @param int $iat
     * @return array
     */
    public static function getAuth(string $token, int $iat = 0): array
    {
        $tokenData = self::checkToken($token, $iat);

        return $tokenData['auth'] ?? [];
    }

    /**
     * 获取token数据（token可能已过期）
     *
     * @param $token
     *
     * @return array
     */
    public static function getTokenData($token): array
    {
        self::setKey();
        return Encryption::decrypt($token, self::$iv);
    }

    private static function checkToken($token, $iat = 0): array
    {
        try {
            if (!$tokenData = self::getTokenData($token)) {
                throw new \Exception('token错误');
            }
            if (!empty($iat) && $tokenData['iat'] < $iat) {
                throw new \Exception('token已过期');
            }
            if ($tokenData['exp'] < time()) {
                throw new \Exception('token已过期');
            }
            if ($tokenData['aud'] != self::$aud) {
                throw new \Exception('接收者错误');
            }

            return $tokenData;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 如果设置了key则设置加密
     * @return bool
     */
    protected static function setKey(): bool
    {
        //如果设置了key则设置加密
        if (!empty(self::$key)) {

            return Encryption::set('key', self::$key);

        }

        return true;
    }

}