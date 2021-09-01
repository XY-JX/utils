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

    protected $prefix = 'delay_queue:';
    protected $redis = null;
    protected $key = '';

    /**
     * 构造
     * @param string $queue 队列名称
     * @param array $config host:地址（127.0.0.1） port:端口（6379） timeout:超时时间（秒） auth:密码 select:数据库
     */
    public function __construct(string $queue, array $config = [])
    {

        $this->key = $this->prefix . $queue;
        $this->redis = new \Redis();
        $this->redis->connect($config['host'], $config['port'], $config['timeout']);
        if (isset($config['auth']))
            $this->redis->auth($config['auth']);
        if (isset($config['select']))
            $this->redis->select($config['select']);
    }

    /**
     * 删除已完成的任务
     * @param $value
     * @return int
     */
    public function delTask($value)
    {
        return $this->redis->zRem($this->key, $value);
    }

    /**
     * 获取需要执行的任务
     * @param int $limit 记录数默认1
     * @return array
     */
    public function getTask($limit = 1)
    {
        //获取任务，以0和当前时间为区间，返回（$limit）条记录
        return $this->redis->zRangeByScore($this->key, 0, time(), ['limit' => [0, $limit]]);
    }

    /**
     * 添加需要执行的任务
     * @param string $name 任务名称
     * @param int $time 任务执行时间
     * @param array $data 任务参数
     * @return int
     */
    public function addTask(string $name, int $time, array $data)
    {
        //添加任务，以时间作为score，对任务队列按时间从小到大排序
        return $this->redis->zAdd(
            $this->key,
            $time,
            json_encode([
                'task_name' => $name,
                'task_time' => $time,
                'task_data' => $data,
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 获取数据并从队列中删除
     * @param int $limit
     * @return array
     */
    public function getData($limit = 1)
    {
        $return = [];
        $task = $this->getTask($limit);
        foreach ($task as $value) {
            if ($this->delTask($value)) {
                $return[] = json_decode($value, true);
            }
        }
        return $return;
    }
}



