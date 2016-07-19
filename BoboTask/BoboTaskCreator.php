<?php
namespace Adocwang\Bbt;


class BoboTaskCreator
{
    //定义log类型常量
    public static $LOG_TYPE_ERROR = 'e';
    public static $LOG_TYPE_LOG = 'l';
    public static $LOG_TYPE_WARNING = 'w';

    private $configArray = array(
        //任务的key
        'task_key' => '',
        //任务最大可使用的内存
        'max_memory_usage' => 100000000,
        //日志path
        'log_config_log_path' => '',
        //最大执行的次数,0为无限次
        'max_loop' => 0,
    );

    //memcached的client来读取memcacheq
    private $mq;
    private $logger;
    //当前task的data
    public $nowTaskData;

    public function __construct($config)
    {
        $this->config($config);
        $this->logger = new Logger($this->configArray['logger']);
        $this->mq = new Mq($this->configArray['message_queue']);
    }

    public function config($configData, $value = "")
    {
        if (is_array($configData)) {
            foreach ($configData as $key => $value) {
                $this->setConfigKey($key, $value);
            }
            return true;
        } elseif (!empty($value)) {
            return $this->setConfigKey($configData, $value);
        } else {
            if (isset($this->configArray[$configData])) {
                return $this->configArray[$configData];
            } else {
                return null;
            }
        }
    }

    private function setConfigKey($key, $value)
    {
        if (isset($this->configArray[$key]) && is_array($this->configArray[$key])) {
            $this->configArray[$key] = array_merge_recursive($this->configArray[$key], $value);
        } else {
            $this->configArray[$key] = $value;
        }
        if (strcmp($key, "task_key") === 0) {
            $this->configArray['message_queue']['task_key'] = $value;
            $this->configArray['logger']['task_key'] = $value;
        }
        return $value;
    }

    public function pushToQueue($data)
    {
        $this->mq->push(serialize($data));
    }

    public function startTask($taskCall)
    {
        $this->onStart();
        do {
//            $this->nowTaskData=$this->popData();
            if ($this->countQueue() > 0) {
                $this->beforeOneTask();
                $taskCall();
                $this->checkMemoryOut();
                $this->afterOneTask();
            } else {
                $this->writeLog('task_state', 'no tasks', self::$LOG_TYPE_LOG);
                break;
            }
            usleep(100);
        } while (1);
        $this->stopTask();
    }

    public function writeLog($tag, $data, $type = "l")
    {
        return $this->logger->writeLog($tag, $data, $type);
    }

    public function checkMemoryOut()
    {
        $usage = memory_get_usage();
        if ($usage >= $this->config('max_memory_usage')) {
            $this->writeLog('task_state', 'memory out', self::$LOG_TYPE_WARNING);
            exit;
        }
    }

    public function countQueue()
    {
        return $this->mq->count();
    }

    public function popFromQueue($length = 1)
    {
        $data = [];
        if ($length > 1) {
            for ($i = 0; $i < $length; $i++) {
                $data[] = unserialize($this->mq->pop());
            }
        } else {
            $data = unserialize($this->mq->pop());
        }
        $this->nowTaskData = $data;
        return $data;
    }

    public
    function stopTask()
    {
        $this->onStop();
        exit();
    }

    public
    function __destruct()
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
    public
    function beforeOneTask()
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