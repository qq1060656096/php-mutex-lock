<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 09:35
 */

namespace Zwei\Sync\Mutex;

use Zwei\Sync\Helper\Helper;
use Zwei\Sync\LockAbstract;
use Zwei\Sync\LockRepositoryInterface;

/**
 * 业务互斥锁(非堵塞)
 *
 * Class BusinessMutex
 * @package Zwei\Sync\Mutex
 */
class BusinessMutex extends LockAbstract
{
    protected $operationName;
    protected $args;
    
    /**
     * BusinessMutex constructor.
     * @param LockRepositoryInterface $lockRepositoryInterface
     * @param integer $expired milliseconds
     * @param string $operationName operation name
     * @param string|integer|float ...$args
     */
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $expired, $operationName, ...$args)
    {
        $this->lockRepositoryInterface = $lockRepositoryInterface;
        $this->expired = $expired;
        $this->operationName = $operationName;
        $this->args = $args;
        $this->setName();
    }
    
    protected function setName()
    {
        $index = 0;
        foreach ($this->args as $arg) {
            $this->names[$index] = Helper::generateLockName($this->operationName, $arg);
            $index ++;
        }
    }
}
