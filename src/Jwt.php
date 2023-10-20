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
     * 面向的用户
     *
     * @var string
     */
    protected static $sub = 'xy-jx';
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
    protected static $iv = '@user@token@jwt@';

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
     * @return array
     */
    public static function getToken(array $user, array $auth = [], int $expire = 0): array
    {
        $time = time();
        $sigData = [
            'iss' => self::$iss,//签发者
            'aud' => self::$aud,//接收者
            'sub' => self::$sub,//面向的用户
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
        $data['token'] = Encryption::encrypt($sigData, self::$iv);

        return $data;
    }

    /**
     * 获取用户信息
     *
     * @param string $token
     * @param null $uuid
     * @param array $auth
     *
     * @return array
     */
    public static function getUser(string $token, &$uuid = null, array &$auth = []): array
    {
        $tokenData = self::checkToken($token);
        $uuid = $tokenData['uuid'] ?? null;
        $auth = $tokenData['auth'] ?? [];

        return $tokenData['user'] ?? [];
    }

    /**
     * 获取uuid
     *
     * @param $token
     *
     * @return mixed|null
     */
    public static function getUUID($token)
    {
        $tokenData = self::checkToken($token);

        return $tokenData['uuid'] ?? null;
    }

    /**
     * 获取type
     *
     * @param $token
     *
     * @return mixed|null
     */
    public static function getType($token)
    {
        $tokenData = self::checkToken($token);

        return $tokenData['type'] ?? null;
    }

    /**
     * 获取到期时间戳
     *
     * @param $token
     *
     * @return int
     */
    public static function getExp($token): int
    {
        $tokenData = self::checkToken($token);

        return $tokenData['exp'] ?? 0;
    }

    /**
     * 获取授权信息
     *
     * @param $token
     *
     * @return array
     */
    public static function getAuth($token): array
    {
        $tokenData = self::checkToken($token);

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
        return Encryption::decrypt($token, self::$iv);
    }

    private static function checkToken($token): array
    {
        try {
            if (!$tokenData = Encryption::decrypt($token, self::$iv)) {
                throw new \Exception('token错误');
            }
            if ($tokenData['exp'] < time()) {
                throw new \Exception('token已过期');
            }
            if ($tokenData['aud'] != self::$aud) {
                throw new \Exception('接收者错误');
            }
            if ($tokenData['sub'] != self::$sub) {
                throw new \Exception('面向的用户错误');
            }

            return $tokenData;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 重置密钥 不建议使用
     *
     * @return false|int
     */
    public static function resetKey()
    {
        $f = './src/Jwt.php';
        $s = uniqid(mt_rand(100, 999));
        $fileGet = file_get_contents($f);
        $file = str_replace(self::$iv, $s, $fileGet);

        return file_put_contents($f, $file);
    }
}