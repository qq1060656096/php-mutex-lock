<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-23
 * Time: 17:45
 */
namespace Zwei\Sync\Exception;

/**
 * 加锁失败异常
 *
 * Class LockFailException
 * @package Zwei\MutexLock\Exception
 */
class LockFailException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws LockFailException
     */
    public static function fail($message = "lock.fail", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
}
