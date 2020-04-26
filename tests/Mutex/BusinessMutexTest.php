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
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\Mutex\BusinessMutex;
use Zwei\Sync\Tests\Repository\RedisRepositoryTrait;

class BusinessMutexTest extends TestCase
{
    use RedisRepositoryTrait;
    
    /**
     * @return \Zwei\Sync\LockRepositoryInterface
     */
    public function getLockRepository()
    {
        return $this->getRedisLockRepository();
    }
    
    /**
     * 测试锁名
     */
    public function testGetName()
    {
        $expired = 1;
        $operationName = 'phpunit.20200424';
        $ids = [1];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
        $this->assertEquals(['phpunit.20200424:1'], $obj->getNames());
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
        $ids = [21, 22];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
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
        $ids = [32, 32];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
        $obj->lock();
        $obj->lock();
    }
    
    /**
     * 测试加锁解锁
     */
    public function testLockAndUnlock()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $ids = [41, 42];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
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
     * @throws Zwei\Sync\Exception\LockParamException
     */
    public function testSynchronized()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $ids = [51, 52];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
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
     * @expectedException Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testSynchronizedUnlockTimeOut()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $ids = [61, 62];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
        $testCase = $this;
        $obj->synchronized(function() use ($testCase) {
            sleep(6);
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
        $ids = [71, 72];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
        $obj->lock();
        sleep(6);
        $obj->unlock();
    }
    
    /**
     * 未加锁时, 解锁异常
     * @expectedException  \Zwei\Sync\Exception\NoLockUnLockFailException
     */
    public function testUnlockNoLockUnLockFailException()
    {
        $expired = Helper::secondsToMilliseconds(5);
        $operationName = 'phpunit.20200424';
        $ids = [81, 82];
        $obj = new BusinessMutex($this->getLockRepository(), $expired, $operationName, ...$ids);
        $obj->unlock();
    }
}
