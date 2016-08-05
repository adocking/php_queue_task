<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "vendor/autoload.php";
$iniPath = "/etc/pat_daemon.conf";
if (!file_exists($iniPath)) {
    $iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pat_daemon.json";
}
$scheduler = new \Adocwang\Pat\PhpAsyncTaskScheduler($iniPath);
$scheduler->main($argv);