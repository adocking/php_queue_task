<?php
include_once 'vendor/autoload.php';
$iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.json";
$scheduler = new \Adocwang\Bbt\BoboTaskScheduler($iniPath);
$scheduler->main($argv);