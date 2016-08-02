<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/19/16
 * Time: 21:58
 */

namespace Adocwang\Pat\QueueDrivers;


class Redis implements QueueDriverInterface
{
    private static $redisObj;

    private $server;

    private $port;

    public function __construct($config)
    {
        $this->server = $config['host'];
        $this->port = $config['port'];
        if (empty(self::$redisObj)) {
            $this->initRedis();
        }
    }

    public function initRedis()
    {
        self::$redisObj = new \Redis();
        self::$redisObj->popen($this->server, $this->port);
    }

    public function getObj()
    {
        if (empty(self::$redisObj)) {
            $this->initRedis();
        }
        return self::$redisObj;
    }

    /**
     * get top data of queue
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function pop($key)
    {
        // TODO: Implement pop() method.
        $res = $this->getObj()->rpop($key);
        return unserialize($res);
    }

    /**
     * get top data of queue,if there is no data in queue,this function will block
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function blPop($key)
    {
        // TODO: Implement blPop() method.
        $res = $this->getObj()->brpop($key);
        return unserialize($res);
    }

    /**
     * put data to the bottom of queue
     *
     * @param $key string name of queue
     * @param $data mixed
     * @return boolean
     */
    public function push($key, $data)
    {
        // TODO: Implement push() method.
        return $this->getObj()->lpush($key, serialize($data));
    }

    /**
     * count queue's length
     *
     * @param $key string name of queue
     * @return int
     */
    public function count($key)
    {
        // TODO: Implement count() method.
        return $this->getObj()->lSize($key);
    }

    /**
     * clean all data in queue
     *
     * @param $key string name of queue
     * @return boolean
     */
    public function clear($key)
    {
        // TODO: Implement clear() method.
        return $this->getObj()->del($key);
    }
}