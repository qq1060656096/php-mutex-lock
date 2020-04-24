<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:52
 */

namespace Zwei\Sync\Mutex;

/**
 * 订单业务锁
 *
 * Class OrderBusinessMutex
 * @package Zwei\Sync\Mutex
 */
class OrderBusinessMutex extends BusinessMutex
{
    protected $operationName = 'order';
    
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $orderId, $expired)
    {
        $operationName = "order";
        $args = [
            $orderId
        ];
        parent::__construct($lockRepositoryInterface, $expired, $operationName, ...$args);
    }
}
