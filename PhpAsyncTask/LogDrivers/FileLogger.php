<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/14/16
 * Time: 17:42
 */

namespace Adocwang\Pat\LogDrivers;


class FileLogger implements LogDriverInterface
{
    private $handler;

    public function __construct($config)
    {
        if (empty($config['log_path'])) {
            throw new \Exception('no log path');
        }
        $this->handler = fopen($config['log_path'], 'a+');
    }

    public function write($lineContent)
    {
        fwrite($this->handler, $lineContent);
    }

    public function __destruct()
    {
        fclose($this->handler);
    }
}