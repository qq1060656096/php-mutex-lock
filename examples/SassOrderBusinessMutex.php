<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-26
 * Time: 09:36
 */

namespace Zwei\Sync\Examples;


use Zwei\Sync\LockRepositoryInterface;
use Zwei\Sync\Mutex\BusinessMutex;

/**
 * sass平台订单业务锁
 *
 * Class SassOrderBusinessMutex
 * @package Zwei\Sync\Mutex\Examples
 */
class SassOrderBusinessMutex extends BusinessMutex
{
    protected $operationName = 'sassOrder';
    
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $expired, $companyId, $orderId)
    {
        $operationName = $this->operationName;
        $args = [
            $companyId,
            $orderId
        ];
        parent::__construct($lockRepositoryInterface, $expired, $operationName, ...$args);
    }
}
