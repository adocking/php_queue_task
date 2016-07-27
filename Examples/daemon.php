<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."vendor/autoload.php";
$iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "daemon_config.json";
$scheduler = new \Adocwang\Pat\PhpAsyncTaskScheduler($iniPath);
$scheduler->main($argv);