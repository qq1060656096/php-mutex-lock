<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 20:31
 */

namespace Zwei\Sync\Exception;


class NoLockUnLockFailException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @throws NoLockUnLockFailException
     */
    public static function noLock($message = "unlock.noLock", $code = 0, \Throwable $previous = null)
    {
        throw new static($message, $code, $previous);
    }
}
