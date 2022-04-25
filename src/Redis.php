<?php
// +----------------------------------------------------------------------
// | DATE: 2021/9/9 14:33
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  PhpStorm Redis.php  
// +----------------------------------------------------------------------

namespace xy_jx\Utils;


class Redis
{

    protected static $handler;
    protected static $persistent_id;
    public static $duration = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];
    /**
     * @var array
     */
    protected static $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,//是否开启长链接
        'persistent_id' => '',//用于指定长链接id 特殊情况下使用
        'prefix' => '',
        'tag_prefix' => 'tag:',
        'serialize' => [],
    ];

    /**
     * 架构函数
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        // 加载配置参数，替换默认参数
        if (!empty($options)) self::$options = array_merge(self::$options, $options);

        // 如果链接存在并且链接参数一致复用链接
        $persistent_id = self::$options['persistent_id'] ?: self::$options['host'] . self::$options['port'] . self::$options['select'];
        if (self::$handler && self::$persistent_id == $persistent_id) return true;
        self::$persistent_id = $persistent_id;

        if (extension_loaded('redis')) {

            self::$handler = new \Redis;

            if (self::$options['persistent']) {
                self::$handler->pconnect(self::$options['host'], self::$options['port'], self::$options['timeout'], $persistent_id);
            } else {
                self::$handler->connect(self::$options['host'], self::$options['port'], self::$options['timeout']);
            }

            if (self::$options['password'])
                self::$handler->auth(self::$options['password']);

            if (self::$options['select'])
                self::$handler->select(self::$options['select']);

        } else {
            throw new \Exception('not support: redis');
        }
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     * @return \Redis
     */
    public static function handler(): \Redis
    {
        return self::$handler;
    }

    /**
     * 访问限制
     * @param string $key ip|uid
     * @param int $limit 限制次数
     * @param string $time 时间范围 s m h d
     * @return bool
     */
    public static function restrict(string $key, int $limit = 3, string $time = 's'): bool
    {
        $key = 'throttle_:' . $key;
        if (self::$handler->get($key)) {
            if (self::$handler->incr($key) > $limit) return false; //键值递增,大于限制
        } else {
            self::$handler->set($key, 1, ['nx', 'ex' => self::$duration[$time]]);
        }
        return true;
    }


}