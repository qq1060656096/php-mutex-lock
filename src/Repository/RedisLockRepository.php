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
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\LockRepositoryInterface;

class RedisLockRepository implements LockRepositoryInterface
{
    /**
     * @var Redis
     */
    protected $redis;
    
    private $lockLuaScript = null;
    
    private $unlockLuaScript = null;
    
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
        $luaDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'lua'.DIRECTORY_SEPARATOR;
        $this->lockLuaScript = file_get_contents($luaDir."redis.lock.tpl.lua");
        $this->unlockLuaScript = file_get_contents($luaDir."redis.unlock.tpl.lua");
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
     * @inheritdoc
     */
    public function lock($clientId, $expired, ...$lockNames)
    {
        Helper::validateLockExpired($expired);
        Helper::validateLockNames(...$lockNames);
        $numKeys = count($lockNames);
        $values = [
            $clientId,
        ];
        $args = array_merge($lockNames, $values);
        $lockLuaScript = sprintf($this->lockLuaScript, $expired);
        $intResult = $this->getRedis()->eval($lockLuaScript, $args, $numKeys);
        if ($intResult < 1) {
            LockFailException::fail();
        }
        return $intResult;
    }
    
    /**
     * 解锁
     *
     * @inheritdoc
     */
    public function unlock($clientId, ...$lockNames)
    {
        Helper::validateLockNames(...$lockNames);
        $numKeys = count($lockNames);
        $values = [
            $clientId
        ];
        $args = array_merge($lockNames, $values);
        $result = $this->getRedis()->eval($this->unlockLuaScript, $args, $numKeys);
        if ($result < 1) {
            UnLockTimeoutException::timeout();
        }
        return $result;
    }
}
