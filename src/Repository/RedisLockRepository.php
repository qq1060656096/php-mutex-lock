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
     * @inheritdoc
     */
    public function lock($clientId, $expired, ...$lockNames)
    {
        Helper::validateLockExpired($expired);
        Helper::validateLockNames(...$lockNames);
        $numKeys = count($lockNames);
        $lua = [];
        $lua[] = "local keysOkCount = 0;";
        $lua[] = "local operationOk = false;";
        $index = 0;
        $keys = [];
        $values = [];
        $delKeys = [];
        foreach ($lockNames as $key) {
            $value = $clientId;
            $index ++;
            $lua[] = <<<str
operationOk  = redis.call('set', KEYS[{$index}], ARGV[{$index}], 'PX', {$expired}, 'NX');
str;
            $lua[] = <<<str
if (operationOk) then keysOkCount = keysOkCount + 1 end;
str;
        
            $keys[] = $key;
            $values[] = $value;
            $delKeys[] = "KEYS[{$index}]";
        }
        $delKeysStr = implode(", ", $delKeys);
        $lua[] = <<<str
if (keysOkCount ~= {$numKeys}) then redis.call('del', {$delKeysStr}); keysOkCount = 0;end;
str;
        $lua[] = "return keysOkCount;";
        $luaScript = implode("", $lua);
        $args = array_merge($keys, $values);
        $intResult = $this->getRedis()->eval($luaScript, $args, $numKeys);
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
        $lua = [];
        $lua[] = "local keysDelOkCount = 0;";
        $lua[] = "local tmpVal = nil;";
        $index = 0;
        $keys = [];
        $values = [];
        foreach ($lockNames as $key) {
            $value = $clientId;
            $index ++;
            $lua[] = <<<str
tmpVal  = redis.call('get', KEYS[{$index}]);
str;
            $lua[] = <<<str
if (tmpVal == ARGV[1]) then keysDelOkCount = keysDelOkCount + redis.call('del', KEYS[{$index}]) end;
str;
            $keys[] = $key;
            $values[] = $value;
        }
        $lua[] = "return keysDelOkCount;";
        $luaScript = implode("", $lua);
        $args = array_merge($keys, $values);
        $result = $this->getRedis()->eval($luaScript, $args, $numKeys);
        if ($result < 1) {
            UnLockTimeoutException::timeout();
        }
        return $result;
    }
}
