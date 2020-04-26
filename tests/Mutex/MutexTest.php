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
use Zwei\Sync\Tests\Repository\RedisRepositoryTrait;

class MutexTest extends TestCase
{
    use RedisRepositoryTrait;
    
    /**
     * 测试锁名
     */
    public function testGetName()
    {
        $expired = 1;
        $names = ['phpunit.mutex.20200424.1'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
        $this->assertEquals(['phpunit.mutex.20200424.1'], $obj->getNames());
    }
    
    
    /**
     * 测试加锁
     * @throws
     */
    public function testLock()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $names = ['phpunit.mutex.20200424.2'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
        $obj->lock();
        $this->assertTrue(true);
    }
    
    
    
    /**
     * 测试加锁解锁
     */
    public function testLockAndUnlock()
    {
        $expired = Helper::secondsToMilliseconds(20);
        $names = ['phpunit.mutex.20200424.3'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
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
        $expired = Helper::secondsToMilliseconds(5);
        $names = ['phpunit.mutex.20200424.41', 'phpunit.mutex.20200424.42'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(3);
            $testCase->assertTrue(true);
        });
        $this->assertTrue(true);
    }
    
    /**
     * 测试匿名方法解锁超时
     *
     * @expectedException \Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testSynchronizedUnlockTimeOut()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $names = ['phpunit.mutex.20200424.5'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(7);
            $testCase->assertTrue(true);
        });
        $this->assertTrue(true);
    }
    
    /**
     * 测试解锁超时
     * @expectedException \Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testUnlockTimeout()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $names = ['phpunit.mutex.20200424.6'];
        $obj = new Mutex($this->getRedisLockRepository(), $expired, ...$names);
        $obj->lock();
        sleep(6);
        $obj->unlock();
    }
}
