<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:36
 */
namespace Zwei\Sync\Helper;

class Helper
{
    /**
     * 生成锁名
     *
     * @param string $operation
     * @param string|integer|float ...$args
     * @return string
     */
    public static function generateLockName($operation, ...$args)
    {
        $argStr = implode(':', $args);
        return sprintf("%s:%s", $operation, $argStr);
    }
    
    /**
     * 秒转毫秒
     *
     * @param integer $seconds
     * @return int $milliseconds
     */
    public static function secondsToMilliseconds($seconds)
    {
        $milliseconds = $seconds * 1000;
        return $milliseconds;
    }
    
    /**
     * 获取当前毫秒数
     *
     * @return integer milliseconds
     */
    public static function getNowMilliseconds()
    {
        $milliseconds = microtime(true) * 1000;
        $millisecondsArr = explode('.', $milliseconds);
        return $millisecondsArr[0];
    }
}
