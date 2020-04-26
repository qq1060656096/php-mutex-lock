<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:52
 */

namespace Zwei\Sync\Tests\Mutex;


use Zwei\Sync\Mutex\OrderBusinessMutex;
use Zwei\Sync\Tests\Repository\MySqlRepositoryTrait;

class OrderBusinessMutexTest extends BusinessMutexTest
{
    use MySqlRepositoryTrait;
    
    /**
     * @return \Zwei\Sync\LockRepositoryInterface
     */
    public function getLockRepository()
    {
        return $this->getMySqlLockRepository();
    }
    
    /**
     * 测试锁名
     */
    public function testGetName()
    {
        $expired = 1;
        $orderId = 1;
        $obj = new OrderBusinessMutex($this->getLockRepository(), $expired, $orderId);
        $this->assertEquals(['order:1'], $obj->getNames());
    }
}
