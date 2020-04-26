<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:09
 */

namespace Zwei\Sync\Exception;


class LockTimeoutException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws LockTimeoutException
     */
    public static function timeout($message = "lock.timeout", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
}
