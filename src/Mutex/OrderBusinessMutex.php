<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:52
 */

namespace Zwei\Sync\Mutex;

use Zwei\Sync\LockRepositoryInterface;

/**
 * 订单业务锁
 *
 * Class OrderBusinessMutex
 * @package Zwei\Sync\Mutex
 */
class OrderBusinessMutex extends BusinessMutex
{
    protected $operationName = 'order';
    
    /**
     * 订单业务锁可以锁定多个订单
     *
     * @param LockRepositoryInterface $lockRepositoryInterface
     * @param integer $expired
     * @param integer ...$orderIds
     */
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $expired, ...$orderIds)
    {
        $operationName = $this->operationName;
        $args = $orderIds;
        parent::__construct($lockRepositoryInterface, $expired, $operationName, ...$args);
    }
}
