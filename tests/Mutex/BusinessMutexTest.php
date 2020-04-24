<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 14:04
 */
namespace Zwei\Sync\Tests\Mutex;

use PHPUnit\Framework\TestCase;
use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\Mutex\BusinessMutex;
use Zwei\Sync\Repository\RedisLockRepository;

class BusinessMutexTest extends TestCase
{
    public function getRedis()
    {
        $host = '199.199.199.199';
        $post = 16379;
        $password = '000000';
        $redis = new \Redis();
        $redis->connect($host, $post);
        $redis->auth($password);
        return $redis;
    }
    
    public function getRedisLockRepository()
    {
        $obj = new RedisLockRepository($this->getRedis());
        return $obj;
    }
    
    /**
     * 测试锁名
     */
    public function testGetName()
    {
        $expired = 1;
        $operationName = 'phpunit.20200424';
        $id = 1;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $this->assertEquals('phpunit.20200424:1', $obj->getName());
    }
    
    
    /**
     * 测试加锁
     *
     * @throws LockFailException
     * @throws \Zwei\Sync\Exception\LockParamException
     */
    public function testLock()
    {
        $expired = Helper::secondsToMilliseconds(20);
        $operationName = 'phpunit.20200424';
        $id = 2;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $obj->lock();
        $this->assertTrue(true);
    }
    
    /**
     * 测试二次加锁失败
     *
     * @expectedException \Zwei\Sync\Exception\LockFailException
     */
    public function testLockFail()
    {
        $expired = Helper::secondsToMilliseconds(20);
        $operationName = 'phpunit.20200424';
        $id = 3;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $obj->lock();
        $obj->lock();
    }
    
    /**
     * 测试加锁解锁
     */
    public function testLockAndUnlock()
    {
        $expired = Helper::secondsToMilliseconds(20);
        $operationName = 'phpunit.20200424';
        $id = 4;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $obj->lock();
        $obj->unlock();
        $obj->lock();
        $this->assertTrue(true);
    }
    
    /**
     * 测试匿名方法加锁解锁
     *
     * @throws LockFailException
     * @throws \Throwable
     * @throws \Zwei\Sync\Exception\LockParamException
     */
    public function testSynchronized()
    {
        $expired = Helper::secondsToMilliseconds(60);
        $operationName = 'phpunit.20200424';
        $id = 5;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(20);
            $testCase->assertTrue(true);
        });
        $this->assertTrue(true);
    }
    
    /**
     * 测试匿名方法解锁超时
     *
     * @expectedException Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testSynchronizedUnlockTimeOut()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $id = 6;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(7);
            $testCase->assertTrue(true);
        });
        $this->assertTrue(true);
    }
    
    /**
     * 测试解锁超时
     * @expectedException Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testUnlockTimeout()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $id = 7;
        $obj = new BusinessMutex($this->getRedisLockRepository(), $expired, $operationName, $id);
        $obj->lock();
        sleep(6);
        $obj->unlock();
    }
}
