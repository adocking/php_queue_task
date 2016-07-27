<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/15/16
 * Time: 17:00
 */
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "TestJobs.php";
//$fd=fopen('/tmp/task_log.log',"a+");
//fwrite($fd,"asdasd");
//fclose($fd);
$job = new TestJobs();
$job->doJob1();