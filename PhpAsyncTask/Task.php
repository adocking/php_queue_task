<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 8/4/16
 * Time: 11:57
 */

namespace Adocwang\Pat;


class Task
{
    public $id;
    public $data = array();
    public $executionTime = "";
    public $delay = 0;
    public $createdTime = "";
    public $executionTimestamp = 0;

    function __sleep()
    {
        if (empty($this->id)) {
            $this->id = uniqid('', true);
        }
        if (empty($this->createdTime)) {
            $this->createdTime = date('Y-m-d H:i:s');
        }
        if (!empty($this->executionTime)) {
            $this->executionTimestamp = strtotime($this->executionTime);
        }
        if ($this->delay > 0) {
            $this->executionTimestamp += $this->delay;
        }
        return array('id', 'data', 'executionTimestamp', 'createdTime');
    }
}