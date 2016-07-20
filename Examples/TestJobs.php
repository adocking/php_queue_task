<?php
include_once("../vendor/autoload.php");
include_once("./test_job_config.php");
use Adocwang\Pat\PhpAsyncTaskCreator;

class TestJobs
{
    /**
     * 添加一些测试任务,以及任务数据
     */
    public function addJob()
    {
        $taskClient = new PhpAsyncTaskCreator(TestJobConfig::get());
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
        $taskClient = new PhpAsyncTaskCreator(TestJobConfig::get());
        $taskClient->writeLog('test', 'log test');
        $taskClient->startTask(function () use ($taskClient) {
            $data = $taskClient->popFromQueue();
            $taskClient->writeLog('in_job', serialize($data), PhpAsyncTaskCreator::$LOG_TYPE_LOG);
        });
    }
}