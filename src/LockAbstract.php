<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 09:34
 */

namespace Zwei\Sync;


use Zwei\Sync\Exception\NoLockUnLockFailException;
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Helper\Helper;

class LockAbstract implements LockInterface
{
    protected $name;
    
    protected $expired;
    
    protected $lockRepositoryInterface;
    
    protected $startMilliseconds = 0;
    
    protected $isLocked = false;
    
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @inheritdoc
     */
    public function getExpired()
    {
        return $this->expired;
    }
    
    /**
     * @inheritdoc
     */
    public function getLockRepositoryInterface()
    {
        return $this->lockRepositoryInterface;
    }
    
    /**
     * @inheritdoc
     */
    public function lock()
    {
        $bool = $this->getLockRepositoryInterface()->lock($this->getName(), $this->getExpired());
        $this->isLocked = true;
        return $bool;
    }
    
    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->isLocked;
    }
    /**
     * @inheritdoc
     */
    public function unlock()
    {
        // 防止未加锁,解锁情况
        if (!$this->isLocked()) {
            throw new NoLockUnLockFailException("unlock.fail.noLock");
        }
        $this->isLocked = false;
        // 解锁超时(所超时，解锁导致其他用户加锁被解锁，从而导致更严重的问题)
        if ($this->checkLockTimeOut()) {
            throw new UnLockTimeoutException('unlock.timeout');
        }
        return $this->getLockRepositoryInterface()->unlock($this->getName());
    }
    
    /**
     * @inheritdoc
     */
    public function synchronized(callable $code)
    {
        $codeResult = null;
        $this->lock();
        try {
            $codeResult = $code();
        } catch (\Exception $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $this->unlock();
        }
        return $codeResult;
    }
    
    
    /**
     * @return integer
     */
    protected function getStartMilliseconds()
    {
        return $this->startMilliseconds;
    }
    
    /**
     * @param integer
     */
    protected function setStartMilliseconds()
    {
        $this->startMilliseconds = Helper::getNowMilliseconds();
    }
    
    /**
     * @inheritdoc
     */
    public function checkLockTimeOut()
    {
        if (Helper::getNowMilliseconds() - $this->getStartMilliseconds() >= $this->expired) {
            return true;
        }
        return false;
    }
}
