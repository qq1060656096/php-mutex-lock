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
    
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $expired, $orderId)
    {
        $operationName = $this->operationName;
        $args = [
            $orderId
        ];
        parent::__construct($lockRepositoryInterface, $expired, $operationName, ...$args);
    }
}
