<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 22:31
 */

namespace Zwei\Sync\Tests\Repository;


use Zwei\Sync\Repository\PdoLockRepository;

trait MySqlRepositoryTrait
{
    use MySqlPdoTrait;
    
    /**
     * @return PdoLockRepository
     */
    public function getMySqlLockRepository()
    {
        $obj = new PdoLockRepository($this->getPdo());
        return $obj;
    }
}
