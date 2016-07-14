<?php
include("./BoboTask/BoboTask.php");
use adocwang\bbt\BoboTask;

class TestJobs
{
    public function addJob()
    {
        $config = array(
            'task_key'=>'test_tasks',
            'queue_config' => array(
                'driver' => 'memcacheq',
                'host' => '127.0.0.1',
                'port' => 22201
            ),
            'log_config' => array(
                'driver' => 'file',
                'log_path' => '/tmp/task_log.log',
            ),
            'max_memory_usage' => 64 * 1024 * 1024,
            'max_loop'=>0,
        );
        $taskClient = new BoboTask($config);
        $taskClient->pushData(array('job_id' => 1));
        $taskClient->pushData(array('job_id' => 2));
        $taskClient->pushData(array('job_id' => 3));
        $taskClient->pushData(array('job_id' => 4));
    }

    public function doJob1()
    {
        $config = array(
            'task_key'=>'test_tasks',
            'queue_config' => array(
                'driver' => 'memcacheq',
                'host' => '127.0.0.1',
                'port' => 22201
            ),
            'log_config' => array(
                'driver' => 'file',
                'log_path' => '/tmp/task_log.log',
            ),
            'max_memory_usage' => 64 * 1024 * 1024,
        );
        $taskClient = new BoboTask($config);
        $taskClient->startTask(function () use ($taskClient) {
            $data = $taskClient->popData();
            $taskClient->writeLog('in_job', serialize($data), BoboTask::$LOG_TYPE_LOG);
        });
    }
}

$jobs = new TestJobs();
$jobs->addJob();
$jobs->doJob1();