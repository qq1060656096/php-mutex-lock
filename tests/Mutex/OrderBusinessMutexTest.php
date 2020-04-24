<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:52
 */

namespace Zwei\Sync\Tests\Mutex;


use PHPUnit\Framework\TestCase;
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\Mutex\OrderBusinessMutex;

class OrderBusinessMutexTest extends TestCase
{
    use RedisTrait;
    use RedisRepositoryTrait;
    
    /**
     * 测试锁名
     */
    public function testGetName()
    {
        $expired = 1;
        $orderId = 1;
        $obj = new OrderBusinessMutex($this->getRedisLockRepository(), $expired, $orderId);
        $this->assertEquals('order:1', $obj->getName());
    }
}
