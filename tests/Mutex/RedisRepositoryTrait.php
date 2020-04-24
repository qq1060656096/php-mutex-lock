<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:54
 */

namespace Zwei\Sync\Tests\Mutex;


use Zwei\Sync\Repository\RedisLockRepository;

trait RedisRepositoryTrait
{
    public function getRedisLockRepository()
    {
        $obj = new RedisLockRepository($this->getRedis());
        return $obj;
    }
}
