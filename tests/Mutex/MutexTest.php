<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 14:43
 */

namespace Zwei\Sync\Tests\Mutex;


use PHPUnit\Framework\TestCase;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\Mutex\Mutex;
use Zwei\Sync\Repository\RedisLockRepository;

class MutexTest extends TestCase
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
        $name = 'phpunit.mutex.20200424.1';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
        $this->assertEquals('phpunit.mutex.20200424.1', $obj->getName());
    }
    
    
    /**
     * 测试加锁
     * @throws
     */
    public function testLock()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $name = 'phpunit.mutex.20200424.2';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
        $obj->lock();
        $this->assertTrue(true);
    }
    
    
    
    /**
     * 测试加锁解锁
     */
    public function testLockAndUnlock()
    {
        $expired = Helper::secondsToMilliseconds(20);
        $name = 'phpunit.mutex.20200424.3';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
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
        $expired = Helper::secondsToMilliseconds(20);
        $name = 'phpunit.mutex.20200424.4';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(10);
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
        $name = 'phpunit.mutex.20200424.5';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
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
        $name = 'phpunit.mutex.20200424.6';
        $obj = new Mutex($this->getRedisLockRepository(), $expired, $name);
        $obj->lock();
        sleep(6);
        $obj->unlock();
    }
}
