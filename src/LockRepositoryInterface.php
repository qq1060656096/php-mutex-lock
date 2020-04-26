<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 09:42
 */

namespace Zwei\Sync;


use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\LockParamException;
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Exception\NoLockUnLockFailException;
use Zwei\Sync\Exception\UnLockTimeoutException;

interface LockRepositoryInterface
{
    /**
     * 加锁
     * @param string $clientId client id global unique
     * @param integer $expired expired time(milliseconds)
     * @param string|integer ...$lockNames lock name global unique
     * @return bool
     * @throws LockParamException|LockFailException|LockTimeoutException
     */
    public function lock($clientId, $expired, ...$lockNames);
    
    /**
     * 解锁
     * @param string $clientId
     * @param mixed|string|integer $lockNames
     * @return int
     * @throws LockParamException|NoLockUnLockFailException|UnLockTimeoutException
     */
    public function unlock($clientId, ...$lockNames);
    
}
