<?php
namespace Adocwang\Pat;


class PhpAsyncTaskCreator
{
    //定义log类型常量
    public static $LOG_TYPE_ERROR = 'e';
    public static $LOG_TYPE_LOG = 'l';
    public static $LOG_TYPE_WARNING = 'w';

    private $configArray = array(
        //任务的key
        'task_key' => '',
        //日志path
        'log_config_log_path' => '',
    );

    //memcached的client来读取memcacheq
    private $mq;
    private $logger;

    private $taskArgs;

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

    private function pushToQueue(Task $task)
    {
        return $this->mq->push($task, $this->config('task_key'));
    }

    public function writeLog($tag, $data, $type = "l")
    {
        return $this->logger->writeLog($tag, $data, $type);
    }

    public function countQueue()
    {
        return $this->mq->count();
    }

    public function setTaskKey($taskKey)
    {
        if (!empty($taskKey)) {
            $config['task_key'] = $taskKey;
        } else {
            throw new \Exception('TaskKey is empty');
        }
    }

    public function delayTask(int $seconds)
    {
        if ($seconds < 0) {
            $seconds = 0;
        }
        $this->taskArgs['delay'] = $seconds;
    }

    public function setExecutionTime($date)
    {
        $timestamp = strtotime($date);
        if ($timestamp < time()) {
            $timestamp = time();
        }
        $this->taskArgs['executionTime'] = date('Y-m-d H:i:s', $timestamp);
    }

    public function setData($data)
    {
        $this->taskArgs['data'] = $data;
    }

    public function saveTask()
    {
        $task = new Task();
        $task->delay = $this->taskArgs['delay'];
        $task->executionTime = $this->taskArgs['executionTime'];
        $task->data = $this->taskArgs['data'];
        $this->pushToQueue($task);
    }
}