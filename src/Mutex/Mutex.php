<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 11:38
 */

namespace Zwei\Sync\Mutex;


use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\LockTimeoutException;
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\LockAbstract;
use Zwei\Sync\LockRepositoryInterface;

/**
 * 互斥锁(堵塞)
 *
 * Class Mutex
 * @package Zwei\Sync\Mutex
 */
class Mutex extends LockAbstract
{
    
    /**
     * Mutex constructor.
     * @param LockRepositoryInterface $lockRepositoryInterface
     * @param string $expired milliseconds
     * @param string ...$names
     */
    public function __construct(LockRepositoryInterface $lockRepositoryInterface, $expired, ...$names)
    {
        $this->lockRepositoryInterface = $lockRepositoryInterface;
        $this->expired = $expired;
        $this->names = $names;
        $this->clientId = uniqid('mutex');
    }
    
    /**
     * 如果未获取到锁就堵塞
     * @inheritdoc
     * @throws LockTimeoutException
     */
    public function lock()
    {
        $this->setStartMilliseconds();
        while (true) {
            try {
                return parent::lock();
            } catch (LockFailException $exception) {
                // 加锁失败就堵塞, 加锁超时
                if ($this->checkLockTimeOut()) {
                    LockTimeoutException::timeout();
                }
            }
            
        }
    }
}
