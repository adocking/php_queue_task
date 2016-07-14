<?php
namespace adocwang\bbt;

use adocwang\bbt\drivers\MemcacheQ;

class BoboTask
{
    //定义log类型常量
    public static $LOG_TYPE_ERROR = 'e';
    public static $LOG_TYPE_LOG = 'l';
    public static $LOG_TYPE_WARNING = 'w';

    //memcached的client来读取memcacheq
    private $messageQueueObj;
    //file来做Log
    private $fileHandler;

    //任务的key
    private $task_key;
    //任务最大可使用的内存
    private $max_memory_usage = 100000000;
    //日志path
    private $log_config_log_path;
    //当前task的data
    public $nowTaskData;
    //最大执行的次数
    public $max_loop = 0;

    public function __construct($config)
    {
        if (is_string($config)) {
            $this->task_key = $config;
        } else {
            $this->config($config);
        }
    }

    public function config($configData, $value = "")
    {
        if (is_array($configData)) {
            foreach ($configData as $key => $value) {
                $this->setConfigKey($key, $value);
            }
        } elseif (!empty($value)) {
            $this->setConfigKey($configData, $value);
        } else {
            return $this->$configData;
        }
        return true;
    }

    private function setConfigKey($key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $this->setConfigKey($key . '_' . $subKey, $subValue);
            }
        } else {
            $this->$key = $value;
        }
    }

    public function pushData($data)
    {
        $this->getQueueObj()->push($this->task_key, serialize($data));
    }

    private function getQueueObj()
    {
        if (empty($this->messageQueueObj)) {
            switch ($this->queue_config_driver) {
                case 'memcacheq':
                    include_once "drivers/MemcacheQ.php";
                    if (empty($this->queue_config_host) || empty($this->queue_config_port)) {
                        throw new \Exception('no memcache config');
                    }
                    $this->messageQueueObj = new MemcacheQ($this->queue_config_host, $this->queue_config_port);
                    break;
                default:
                    throw new \Exception('no queue_config_driver');
                    break;
            }
        }
        return $this->messageQueueObj;
    }

    private function connectLog()
    {
        if (empty($this->fileHandler)) {
            if (empty($this->log_config_log_path)) {
                return false;
            }
            if (!file_exists(dirname($this->log_config_log_path))) {
                mkdir(dirname($this->log_config_log_path));
            }
            $this->fileHandler = fopen($this->log_config_log_path, "a+");
        }
        return $this->fileHandler;
    }

    public function startTask($taskCall)
    {
        $this->onStart();
        do {
//            $this->nowTaskData=$this->popData();
            if ($this->countData() > 0) {
                $this->beforeOneTask();
                $taskCall();
                $this->checkMemoryOut();
                $this->afterOneTask();
            } else {
                $this->writeLog('task_state', 'start tasks', self::$LOG_TYPE_LOG);
                break;
            }
            usleep(100);
        } while (1);
        $this->stopTask();
    }

    public function checkMemoryOut()
    {
        $usage = memory_get_usage();
        if ($usage >= $this->max_memory_usage) {
            $this->writeLog('task_state', 'memory out', self::$LOG_TYPE_WARNING);
            exit;
        }
    }

    public function countData()
    {
        return $this->getQueueObj()->count($this->task_key);
    }

    public function popData()
    {
        $data = unserialize($this->getQueueObj()->pop($this->task_key));
        $this->nowTaskData = $data;
        return $data;
    }

    public function writeLog($tag, $data, $type = "l")
    {
        if (empty($tag) || empty($data)) {
            return false;
        }
        $logText = date('Y-m-d H:i:s') . " " . $type . " " . $tag . " " . $data . " " . "\n";
        echo $logText;
        fwrite($this->connectLog(), $logText);
    }

    public function stopTask()
    {
        $this->onStop();
        exit();
    }

    public function __destruct()
    {
        if (!empty($this->fileHandler)) {
            fclose($this->fileHandler);
        }
    }

    /**
     *
     * 下面是events
     *
     *
     */

    /**
     *
     */
    public function beforeOneTask()
    {

    }

    public function afterOneTask()
    {

    }

    public function onStart()
    {
        $this->writeLog('task_state', 'start tasks', self::$LOG_TYPE_LOG);
    }

    public function onStop()
    {
        $this->writeLog('task_state', 'stop tasks', self::$LOG_TYPE_LOG);
    }
}