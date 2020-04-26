<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 09:35
 */

namespace Zwei\Sync;


use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\LockParamException;
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Exception\NoLockUnLockFailException;
use Zwei\Sync\Exception\UnLockTimeoutException;

interface LockInterface
{
    /**
     * @return string
     */
    public function getClientId();
    
    /**
     * @return array [string]
     */
    public function getNames();
    
    /**
     * @return integer Milliseconds
     */
    public function getExpired();
    
    /**
     * @return LockRepositoryInterface
     */
    public function getLockRepositoryInterface();
    
    /**
     * @return bool
     * @throws LockParamException|LockFailException|LockTimeoutException
     */
    public function lock();
    
    /**
     * @return bool
     */
    public function isLocked();
    
    /**
     * @return integer
     * @throws LockParamException|NoLockUnLockFailException|UnLockTimeoutException
     */
    public function unlock();
    
    /**
     * @param callable $code
     * @return mixed
     * @throws LockParamException|LockFailException|LockTimeoutException
     * @throws LockParamException|NoLockUnLockFailException|UnLockTimeoutException
     * @throws \Exception
     * @throws \Throwable
     */
    public function synchronized(callable $code);
    
    /**
     * @return bool
     */
    public function checkLockTimeOut();
}
