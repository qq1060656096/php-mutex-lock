<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:27
 */

namespace Zwei\Sync\Exception;


class UnLockTimeoutException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws UnLockTimeoutException
     */
    public static function timeout($message = "unlock.timeout", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
}
