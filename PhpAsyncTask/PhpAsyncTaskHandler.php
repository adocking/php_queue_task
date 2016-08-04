<?php
namespace Adocwang\Pat;


class PhpAsyncTaskHandler
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

    public function __construct($config, $taskKey)
    {
        if (!empty($taskKey)) {
            $config['task_key'] = $taskKey;
        } else {
            throw new \Exception('TaskKey is empty');
        }
        $this->config($config);
        $this->logger = new Logger($this->configArray['logger']);
        $this->mq = new Mq($this->configArray['message_queue']);
//        $this->writeLog('task_state', 'Creator start', self::$LOG_TYPE_LOG);
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
        return $this->mq->push($data);
    }

    public function startTask($limit = 1)
    {
        $this->onStart();
        do {
//            $this->nowTaskData=$this->popData();
            $queueCount = $this->countQueue();
            if ($queueCount > 0) {
                $this->beforeOneTask();
                //$taskCall();
                yield $this->popFromQueue($limit);
//                yield $this->popFromQueue();
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

    /**
     * pop data from the queue
     * @param int $limit the limit of the datas,if $limit=1,return the data,else return an array of datas,default id 1.
     * @return array|mixed
     */
    public function popFromQueue($limit = 1)
    {
        $data = [];
        if ($limit < 1) {
            $limit = 1;
        }
        $time = time();
        $ids = array();
        //get data in loop
        do {
            $loop = false;
            $tmpTask = $this->mq->pop();
            if (!empty($tmpTask) || $tmpTask instanceof Task) {
                if (!in_array($tmpTask->id, $ids)) {
                    $ids[] = $tmpTask;
                } else {
                    $loop = false;
                }
                if ($tmpTask->executionTimestamp < $time) {
                    $data[] = $tmpTask->data;
                    if (count($data) >= $limit) {
                        $loop = false;
                    }
                } else {
                    $this->mq->push($tmpTask);
                }
            } else {
                $loop = false;
            }
        } while ($loop);


        if ($limit == 1) {
            $data = $data[0];
        }

        $this->nowTaskData = $data;
        return $data;
    }

    public function stopTask()
    {
        $this->onStop();
        exit();
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