<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/29/16
 * Time: 14:27
 */

namespace Adocwang\Pat\QueueDrivers;


class MQException extends \Exception
{
    function __construct($message)
    {
        parent::__construct($message);
    }
}