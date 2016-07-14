<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/13/16
 * Time: 15:26
 */
namespace Adocwang\Bbt\QueueDrivers;

interface MessageQuery
{
    /**
     * get top data of queue
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function pop($key);

    /**
     * get top data of queue,if there is no data in queue,this function will block
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function blPop($key);


    /**
     * put data to the bottom of queue
     *
     * @param $key string name of queue
     * @param $data mixed
     * @return boolean
     */
    public function push($key,$data);

    /**
     * count queue's length
     *
     * @param $key string name of queue
     * @return int
     */
    public function count($key);

    /**
     * clean all data in queue
     *
     * @param $key string name of queue
     * @return boolean
     */
    public function clear($key);
}