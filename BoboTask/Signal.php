<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/15/16
 * Time: 11:53
 */

namespace Adocwang\Bbt;


final class Signal
{
    protected static $sigNum = 0;
    protected static $ini = null;

    public static function set($sigNum)
    {
        self::$sigNum = $sigNum;
    }

    public static function get()
    {
        return (self::$sigNum);
    }

    public static function reset()
    {
        self::$sigNum = 0;
    }
}