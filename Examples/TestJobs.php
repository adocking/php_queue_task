<?php
include_once("../vendor/autoload.php");
include_once("./test_job_config.php");
use Adocwang\Pat\PhpAsyncTaskCreator;

class TestJobs
{
    public function testJobFunc()
    {
        return "this is a function in testJob";
    }

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
     * 设置执行任务要做的事,通过迭代yield返回的生成器
     *
     *
     */
    public function doJob1()
    {
        $taskClient = new PhpAsyncTaskCreator(TestJobConfig::get());
        $taskClient->writeLog('test', 'log test');
        $controller = $this;
//        var_dump($taskClient->startTask() instanceof Iterator);
//        exit();
        foreach ($taskClient->startTask(2) as $data) {
            $taskClient->writeLog('in_job', serialize($data), PhpAsyncTaskCreator::$LOG_TYPE_LOG);
            $taskClient->writeLog('in_job', $controller->testJobFunc(), PhpAsyncTaskCreator::$LOG_TYPE_LOG);
        }
    }
}