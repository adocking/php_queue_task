<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/15/16
 * Time: 09:43
 */

namespace Adocwang\Pat;


use Adocwang\Pat\QueueDrivers\MemcacheQ;
use Adocwang\Pat\QueueDrivers\MQException;
use Adocwang\Pat\QueueDrivers\Mysql;
use Adocwang\Pat\QueueDrivers\Redis;

class Mq
{
    private $instance;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->getInstance();
    }

    public function getInstance()
    {
        if (empty($this->instance)) {
            switch ($this->config['driver']) {
                case 'memcacheq':
                    if (empty($this->config['host']) || empty($this->config['port'])) {
                        throw new \Exception('no memcacheq host or port');
                    }
                    $this->instance = new MemcacheQ($this->config);
                    break;
                case 'redis':
                    if (empty($this->config['host']) || empty($this->config['port'])) {
                        throw new \Exception('no redis host or port');
                    }
                    $this->instance = new Redis($this->config);
                    break;
                case 'mysql':
                    $configCheckKeys = array('host', 'user', 'db');
                    foreach ($configCheckKeys as $configCheckKey) {
                        if (empty($this->config[$configCheckKey])) {
                            throw new \Exception('no mysql ' . $configCheckKey);
                        }
                    }
                    try {
                        $this->instance = new Mysql($this->config);
                    } catch (MQException $e) {
                        throw $e;
                    }
                    break;
                default:
                    throw new \Exception('no queue config driver');
                    break;
            }
        }
        return $this->instance;
    }

    public function pop($task_key = "")
    {
        if (!empty($task_key)) {
            $this->config['task_key'] = $task_key;
        }
        return unserialize($this->getInstance()->pop($this->config['task_key']));
    }

    public function push(Task $task, $task_key = "")
    {
        if (!empty($task_key)) {
            $this->config['task_key'] = $task_key;
        }
        return $this->getInstance()->push($this->config['task_key'], serialize($task));
    }

    public function count($task_key = "")
    {
        if (!empty($task_key)) {
            $this->config['task_key'] = $task_key;
        }
        return $this->getInstance()->count($this->config['task_key']);
    }
}