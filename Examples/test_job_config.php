<?php

/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/20/16
 * Time: 15:15
 */
Class TestJobConfig
{
    public static function get()
    {
        $config = array(
            'message_queue' => array(
//                'driver' => 'memcacheq',
//                'host' => '127.0.0.1',
//                'port' => 22201

//                'driver' => 'redis',
//                'host' => '127.0.0.1',
//                'port' => 6379
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => 'root',
                'db' => 'test',
            ),
            'logger' => array(
                'driver' => 'file',
                'log_path' => '/tmp/task_log.log',
            ),
            'max_memory_usage' => 64 * 1024 * 1024,
        );
        return $config;
    }
}
