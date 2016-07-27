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
$res = $mq->push('test_tasks',array('foo'=>'bar'));
echo var_export($res)."\n";
$res = $mq->count('test_tasks');
echo var_export($res)."\n";
$res = $mq->pop('test_tasks');
echo var_export($res)."\n";
$res = $mq->count('test_tasks');
echo var_export($res)."\n";
$res = $mq->clear('test_tasks');
echo var_export($res)."\n";