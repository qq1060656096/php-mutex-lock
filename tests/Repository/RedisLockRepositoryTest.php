<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-26
 * Time: 21:11
 */
namespace Zwei\Sync\Tests\Repository;


use PHPUnit\Framework\TestCase;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\Repository\RedisLockRepository;

class RedisLockRepositoryTest extends TestCase
{
    use RedisTrait;
    
    public function dataProviderTestLockParamException()
    {
        $data = [];
    
        $expired = [];
        $lockNames = [];
        $data[] = [$expired, $lockNames];
    
        $expired = 0;
        $lockNames = [];
        $data[] = [$expired, $lockNames];
    
        $expired = 1;
        $lockNames = [];
        $data[] = [$expired, $lockNames];
    
        $expired = 1;
        $lockNames = [
            1,
            ['a'],
            new static(),
        ];
        $data[] = [$expired, $lockNames];
    
        $expired = 1;
        $lockNames = [
            ['a'],
            new static(),
        ];
        $data[] = [$expired, $lockNames];
    
        $expired = 1;
        $lockNames = [
            new static(),
        ];
        $data[] = [$expired, $lockNames];
        return $data;
    }
    
    /**
     * @dataProvider dataProviderTestLockParamException
     * @expectedException  Zwei\Sync\Exception\LockParamException
     */
    public function testLockParamException($expired, $lockNames)
    {
        $redis = $this->getRedis();
        $repository = new RedisLockRepository($redis);
        $clientId = 'phpunit.RedisLockRepository.clientId.1';
        $result = $repository->lock($clientId, $expired, ...$lockNames);
        $this->assertEquals(2, $result);
    }
    
    public function testLock()
    {
        $redis = $this->getRedis();
        $repository = new RedisLockRepository($redis);
        $clientId = 'phpunit.RedisLockRepository.clientId.1';
        $expired = Helper::secondsToMilliseconds(5);
        $lockNames = [
            'phpunit.RedisLockRepository.11',
            'phpunit.RedisLockRepository.12',
        ];
        $result = $repository->lock($clientId, $expired, ...$lockNames);
        $this->assertEquals(2, $result);
    }
    
    /**
     * @expectedException Zwei\Sync\Exception\UnLockTimeoutException
     */
    public function testUnLockTimeoutException()
    {
        $redis = $this->getRedis();
        $repository = new RedisLockRepository($redis);
        $clientId = 'phpunit.RedisLockRepository.clientId.2';
        $lockNames = [
            'phpunit.RedisLockRepository.21',
            'phpunit.RedisLockRepository.22',
        ];
        $result = $repository->unlock($clientId, ...$lockNames);
        $this->assertEquals(2, $result);
    }
    
    public function testLockAndUnlock()
    {
        $redis = $this->getRedis();
        $repository = new RedisLockRepository($redis);
        $expired = Helper::secondsToMilliseconds(5);
        $clientId = 'phpunit.RedisLockRepository.clientId.3';
        $lockNames = [
            'phpunit.RedisLockRepository.31',
            'phpunit.RedisLockRepository.32',
        ];
        $result = $repository->lock($clientId, $expired, ...$lockNames);
        $this->assertEquals(2, $result);
        $result = $repository->unlock($clientId, ...$lockNames);
        $this->assertEquals(2, $result);
    
    }
}
