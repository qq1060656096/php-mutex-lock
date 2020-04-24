<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 22:31
 */

namespace Zwei\Sync\Tests\Mutex;


use Zwei\Sync\Repository\MySqlLockRepository;

trait MySqlRepositoryTrait
{
    use MySqlPdoTrait;
    
    public function getLockRepository()
    {
        $obj = new MySqlLockRepository($this->getPdo());
        return $obj;
    }
}
