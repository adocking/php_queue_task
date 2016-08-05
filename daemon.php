<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "vendor/autoload.php";
$iniPath = "/etc/pat_daemon.conf";
if (!file_exists($iniPath)) {
    $iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pat_daemon.conf";
}
$scheduler = new \Adocwang\Pat\PhpAsyncTaskScheduler($iniPath);
$scheduler->main($argv);