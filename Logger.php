<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/15/16
 * Time: 09:42
 */

namespace Adocwang\Pat;


use Adocwang\Pat\LogDrivers\FileLogger;

class Logger
{
    private $instance;
    private $config;
    private $needEcho = false;

    public function __construct($config)
    {
        $this->config = $config;
        $this->getInstance();
    }

    public function getInstance()
    {
        if (empty($this->instance)) {
            switch ($this->config['driver']) {
                case 'file':
                    $this->instance = new FileLogger($this->config);
                    break;
                default:
                    throw new \Exception('no log driver');
                    break;
            }
        }
        return $this->instance;
    }

    public function writeLog($tag, $data, $type = "L")
    {
        if (empty($tag) || empty($data)) {
            return false;
        }
        $logText = date('Y-m-d H:i:s') . " " . strtoupper($type) . " [" . $tag . "] " . $data . " " . "\n";
        if ($this->needEcho) {
            echo $logText;
        }
        return $this->getInstance()->write($logText);
    }
}