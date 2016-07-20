<?php
include_once '../vendor/autoload.php';
$iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "daemon_config.json";
$scheduler = new \Adocwang\Pat\PhpAsyncTaskScheduler($iniPath);
$scheduler->main($argv);