<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:36
 */
namespace Zwei\Sync\Helper;

use Zwei\Sync\Exception\LockParamException;

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
    

    /**
     * 验证锁过期时间
     * @param integer $expired expired time(milliseconds)
     * @throws LockParamException
     */
    public static function validateLockExpired($expired)
    {
        if (!is_numeric($expired) || $expired < 1) {
            LockParamException::paramExpiredOverMin();
        }
    }
    
    /**
     * 验证锁名是字符串
     *
     * @param string ...$lockNames
     * @throws LockParamException
     */
    public static function validateLockNames(...$lockNames)
    {
        if (count($lockNames) < 1) {
            LockParamException::paramLockNamesIsEmpty();
        }
        foreach ($lockNames as $name) {
            if (!is_string($name)) {
                LockParamException::paramLockNamesIsNotString();
            }
        }
    }
}
