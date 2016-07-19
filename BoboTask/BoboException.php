<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/16/16
 * Time: 17:39
 */

namespace Adocwang\Bbt;


class BoboException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        echo $message;
        parent::__construct($message, $code, $previous);
    }
}