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
        'persistent' => false,
        'prefix' => '',
        'tag_prefix' => 'tag:',
        'serialize' => [],
    ];

    /**
     * 架构函数
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            self::$options = array_merge(self::$options, $options);
        }
        if (extension_loaded('redis')) {
            self::$handler = new \Redis;
            if (self::$options['persistent']) {
                self::$handler->pconnect(self::$options['host'], (int)self::$options['port'], (int)self::$options['timeout'], 'persistent_id_' . self::$options['select']);
            } else {
                self::$handler->connect(self::$options['host'], (int)self::$options['port'], (int)self::$options['timeout']);
            }
            if (!self::$options['password']) {
                self::$handler->auth(self::$options['password']);
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach (self::$options as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
                    $params[$key] = $val;
                    unset(self::$options[$key]);
                }
            }
            if (!self::$options['password']) {
                unset(self::$options['password']);
            }
            self::$handler = new \Predis\Client(self::$options, $params);

            self::$handler['prefix'] = '';
        } else {
            throw new \Exception('not support: redis');
        }
        if (self::$options['select']) {
            self::$handler->select((int)self::$options['select']);
        }
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     * @return \Predis\Client|\Redis
     */
    public static function handler()
    {
        return self::$handler;
    }

    /**
     * 访问限制
     * @param string $key ip|uid
     * @param int $limit 限制次数
     * @param string $time 时间范围 s m h d
     */
    public static function restrict(string $key, int $limit = 3, string $time = 's')
    {
        $key = 'throttle_:' . $key;
        $count = self::$handler->get($key);
        if ($count) {
            self::$handler->incr($key);  //键值递增
            if ($count + 1 > $limit) {
                return false;
            }
        } else {
            self::$handler->set($key, 1, self::$duration[$time]);
        }
        return true;
    }


}