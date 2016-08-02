<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/13/16
 * Time: 15:42
 */

namespace Adocwang\Pat\QueueDrivers;


class MemcacheQ implements QueueDriverInterface
{
    private static $memcacheObj;

    private static $tempData;

    private $server;

    private $port;

    public function __construct($config)
    {
        $this->server = $config['host'];
        $this->port = $config['port'];
        if (empty(self::$memcacheObj)) {
            $this->initMemcached();
        }
    }

    private function initMemcached()
    {
        self::$memcacheObj = new \Memcached();
        self::$memcacheObj->addServer($this->server, $this->port);
    }

    private function getMemcachedObj()
    {
        if (empty(self::$memcacheObj)) {
            $this->initMemcached();
        }
        return self::$memcacheObj;
    }

    /**
     * get top data of queue
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function pop($key)
    {
        $res=$this->getMemcachedObj()->get($key);
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
        $loop = false;
        do {
            $data = $this->pop($key);
            if (empty($data)) {
                $loop = true;
            }
        } while ($loop);

        return $data;
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
        return $this->getMemcachedObj()->set($key, serialize($data));
    }

    /**
     * count queue's length
     *
     * @param $key string name of queue
     * @return int
     */
    public function count($key)
    {
        self::$tempData = $this->pop($key);
        if (!empty(self::$tempData)) {
            $this->push($key, (self::$tempData));
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * clean all data in queue
     *
     * @param $key string name of queue
     * @return boolean
     */
    public function clear($key)
    {
        $this->getMemcachedObj()->delete($key);
    }
}