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

interface LockRepositoryInterface
{
    /**
     * 加锁
     *
     * @param string $lockName
     * @param integer $milliseconds
     * @return bool
     * @throws LockParamException|LockFailException
     */
    public function lock($lockName, $milliseconds);
    
    /**
     * 解锁
     *
     * @param string $lockName
     * @return int
     */
    public function unlock($lockName);
    
}
