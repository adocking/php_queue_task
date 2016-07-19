<?php
include("./vendor/autoload.php");
use Adocwang\Bbt\BoboTaskCreator;

class TestJobs
{
    /**
     * 添加一些测试任务,以及任务数据
     */
    public function addJob()
    {
        $config = array(
            'task_key' => 'test_tasks',
            'message_queue' => array(
                'driver' => 'memcacheq',
                'host' => '127.0.0.1',
                'port' => 22201
            ),
            'logger' => array(
                'driver' => 'file',
                'log_path' => '/tmp/task_log.log',
            ),
            'max_memory_usage' => 64 * 1024 * 1024,
            'max_loop' => 0,
        );
        $taskClient = new BoboTaskCreator($config);
        for ($i = 0; $i < 10; $i++) {
            $taskClient->pushToQueue(array('job_id' => $i));
        }
    }

    /**
     * 设置执行任务要做的事,通过回调的方式
     *
     *
     */
    public function doJob1()
    {
        $config = array(
            'task_key' => 'test_tasks',
            'message_queue' => array(
                'driver' => 'memcacheq',
                'host' => '127.0.0.1',
                'port' => 22201
            ),
            'logger' => array(
                'driver' => 'file',
                'log_path' => '/tmp/task_log.log',
            ),
            'max_memory_usage' => 64 * 1024 * 1024,
        );
        $taskClient = new BoboTaskCreator($config);
        $taskClient->startTask(function () use ($taskClient) {
            $data = $taskClient->popFromQueue();
            $taskClient->writeLog('in_job', serialize($data), BoboTaskCreator::$LOG_TYPE_LOG);
        });
    }
}