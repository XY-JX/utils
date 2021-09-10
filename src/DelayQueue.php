<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/31 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  延迟队列
// +----------------------------------------------------------------------

namespace xy_jx\Utils;

class DelayQueue
{

    protected static $prefix = 'delay_queue:';
    protected static $redis = null;
    protected static $key = '';

    /**
     * 构造
     * @param array $config host:地址（127.0.0.1） port:端口（6379） timeout:超时时间（秒） password:密码 select:数据库
     * @param string $queue 队列名称
     */
    public function __construct(array $config = [], string $queue = 'list')
    {
        self::$key = self::$prefix . $queue;
        $Redis = new Redis($config);
        self::$redis = $Redis::handler();
    }

    /**
     * 删除已完成的任务
     * @param $value
     * @return int
     */
    public static function del($value)
    {
        return self::$redis->zRem(self::$key, $value);
    }

    /**
     * 获取需要执行的任务
     * @param int $limit 记录数默认1
     * @return array
     */
    public static function get($limit = 1)
    {
        //获取任务，以0和当前时间为区间，返回（$limit）条记录
        return self::$redis->zRangeByScore(self::$key, 0, time(), ['limit' => [0, $limit]]);
    }

    /**
     * 添加需要执行的任务
     * @param string $name 任务名称
     * @param int $time 任务执行时间
     * @param array $data 任务参数
     * @return int
     */
    public static function add(string $name, int $time, array $data)
    {
        //添加任务，以时间作为score，对任务队列按时间从小到大排序
        return self::$redis->zAdd(
            self::$key,
            $time,
            json_encode([
                'name' => $name,
                'time' => $time,
                'data' => $data,
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 获取数据并从队列中删除
     * @param int $limit
     * @return array
     */
    public static function getData($limit = 1)
    {
        $return = [];
        $task = self::get($limit);
        foreach ($task as $value) {
            if (self::del($value)) {
                $return[] = json_decode($value, true);
            }
        }
        return $return;
    }
}



