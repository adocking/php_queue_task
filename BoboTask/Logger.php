<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/15/16
 * Time: 09:42
 */

namespace Adocwang\Bbt;


use Adocwang\Bbt\LogDrivers\FileLogger;

class Logger
{
    private $instance;
    private $config;

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

    public function writeLog($tag, $data, $type = "l")
    {
        if (empty($tag) || empty($data)) {
            return false;
        }
        $logText = date('Y-m-d H:i:s') . " " . $type . " " . $tag . " " . $data . " " . "\n";
        echo $logText;
        return $this->getInstance()->write($logText);
    }
}