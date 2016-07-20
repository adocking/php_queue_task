<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/20/16
 * Time: 17:27
 */

include_once "../vendor/autoload.php";
$config = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'root',
    'db' => 'test',
);
$mq = new \Adocwang\Pat\QueueDrivers\Mysql($config);
//$res = $mq->push('test_work',array('foo'=>'bar'));
//$res = $mq->pop('test_work');
$res = $mq->clear('test_work');
print_r($res);