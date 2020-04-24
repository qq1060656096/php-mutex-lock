<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 16:32
 */
namespace Zwei\Sync\Mutex\examples;

use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Mutex\OrderBusinessMutex;

class OrderBusinessMutexExample
{
    /**
     * 订单审核
     * @throws LockTimeoutException
     * @throws \Zwei\Sync\Exception\LockParamException
     */
    public function orderCheck()
    {
        try {
            $orderDemoBusinessMutex = new OrderBusinessMutex();
            $orderDemoBusinessMutex->lock();
            // todo
            $orderDemoBusinessMutex->unlock();
        } catch (LockFailException $exception) {
            // 其他人正在操作, 请稍后在试
        } catch (LockTimeoutException $exception) {
            // 加锁超时
        } catch (UnLockTimeoutException $exception) {
            // 解锁超时
        }
    }
    
    
    /**
     * 订单审核
     * @throws \Throwable
     * @throws \Zwei\Sync\Exception\LockParamException
     */
    public function orderCheckV2()
    {
        try {
            $orderDemoBusinessMutex = new OrderBusinessMutex();
            $orderDemoBusinessMutex->synchronized(function(){
                // todo
            });
        } catch (LockFailException $exception) {
            // 其他人正在操作, 请稍后在试
        } catch (LockTimeoutException $exception) {
            // 加锁超时
        } catch (UnLockTimeoutException $exception) {
            // 解锁超时
        }
    }
}
