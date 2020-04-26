<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 14:04
 */
namespace Zwei\Sync\Tests\Mutex;


use Zwei\Sync\Tests\Repository\MySqlRepositoryTrait;

class MysqlBusinessMutexTest extends BusinessMutexTest
{
    use MySqlRepositoryTrait;
    
    /**
     * @return \Zwei\Sync\LockRepositoryInterface
     */
    public function getLockRepository()
    {
        return $this->getMySqlLockRepository();
    }
}
