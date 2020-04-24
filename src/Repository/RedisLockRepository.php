<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:19
 */

namespace Zwei\Sync\Repository;

use Redis;
use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\LockParamException;
use Zwei\Sync\LockRepositoryInterface;

class RedisLockRepository implements LockRepositoryInterface
{
    /**
     * @var Redis
     */
    protected $redis;
    
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    
    /**
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }
    
    /**
     * 加锁
     *
     * @param string $lockName
     * @param integer $milliseconds
     * @return bool
     * @throws LockParamException|LockFailException
     */
    public function lock($lockName, $milliseconds)
    {
        if (!is_numeric($milliseconds) || $milliseconds < 1) {
            throw new LockParamException("lock.param.milliseconds.error");
        }
        
        $cacheKey = $lockName;
        $value = time();
        $bool = $this->getRedis()->rawCommand("set", $cacheKey, $value, "PX", $milliseconds, "NX");
        if ($bool === true) {
            return true;
        }
        throw new LockFailException("lock.fail");
    }
    
    /**
     * 解锁
     *
     * @param string $lockName
     * @return int
     */
    public function unlock($lockName)
    {
        $del = $this->getRedis()->del($lockName);
        return $del;
    }
}
