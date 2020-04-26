<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-23
 * Time: 17:45
 */
namespace Zwei\Sync\Exception;

class LockParamException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws LockParamException
     */
    public static function paramExpiredOverMin($message = "lock.param.expired.overMin", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
    
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws LockParamException
     */
    public static function paramLockNamesIsEmpty($message = "lock.param.lockNames.isEmpty", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
    
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws LockParamException
     */
    public static function paramLockNamesIsNotString($message = "lock.param.lockNames.isNotString", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
}
