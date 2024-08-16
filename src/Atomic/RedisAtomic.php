<?php

namespace Zwei\Sync\Atomic;

class RedisAtomic {

    /**
     * rPopAutoAck 队列没有数据
     */
    const RPOP_AUTO_ACK_IS_NO_DATA = 1;

    /**
     * rPopAutoAck 队列和ack队列都没有数据
     */
    const RPOP_AUTO_ACK_IS_NO_ACK_DATA = 2;

    /**
     * @var \Redis
     */
    protected $redis;

    protected $startTime = 0;
    protected $endTime = 0;

    public function __construct(\Redis $redis){
        $this->redis = $redis;
    }

    /**
     * @return \Redis
     */
    public function getRedis() {
        return $this->redis;
    }


    /**
     * 原子操作 redis rpop 有数据才执行，没有数据直接返回 null
     * @param string $queueName
     * @param string $askQueueName
     * @param callable $callFunc
     * @param null|mixed $ackResult
     * @param null|mixed $rPopRawValue
     * @return null
     * @throws \RedisException
     */
    public function rPop($queueName, $askQueueName, callable $callFunc, &$ackResult = null, &$rPopRawValue = null) {
        $value = $this->getRedis()->rPopLPush($queueName, $askQueueName);
        $rPopRawValue = $value;
        $result = null;
        // false 的时候表示没有元素了
        if ($value !== false) {
            $result = $callFunc($value);
            // 没有删除的数据，表示没有消费成功。下次执行的时候还会继续消费，直到删除成功
            $ackResult = $this->getRedis()->lRem($askQueueName, $value, 1);
        }
        return $result;
    }

    /**
     * 原子操作 redis rpop ack 有数据才执行，没有数据直接返回 null
     *
     * @param string $askQueueName
     * @param callable $callFunc
     * @param null|mixed $ackResult
     * @param null|mixed $ackRawValue
     * @return null
     * @throws \RedisException
     */
    public function rPopAck($askQueueName, callable $callFunc, &$ackResult = null, &$ackRawValue = null)
    {
        $result = null;
        $value = $this->getRedis()->lIndex($askQueueName, -1);
        $ackRawValue = $value;
        if ($value !== false) {
            $result = $callFunc($value);
            // 没有删除的数据，表示没有消费成功。下次执行的时候还会继续消费，直到删除成功
            $ackResult = $this->getRedis()->lRem($askQueueName, $value, -1);
        }
        return $result;
    }

    /**
     * rPop 自动ack
     * @param int $runCountGcAck 多久处理一次ack队列异常数据
     * @param string $queueName
     * @param string $askQueueName
     * @param callable $callFunc
     * @return int
     * @throws \RedisException
     */
    public function rPopAutoAck($runCountGcAck, $queueName, $askQueueName, callable $callFunc) {
        $this->startTime = microtime(true);
        $count = 0;
        while(true) {
            $count ++;
            $ackResult = null;
            $rPopRawValue = null;
            $this->rPop($queueName, $askQueueName, function ($data) use ($callFunc) {
                $callFunc($data);
            }, $ackResult, $rPopRawValue);

            // 每执行多少次，就处理一次ack队列异常数据
            if ($count % $runCountGcAck === 0) {
                $this->rPopAck($askQueueName, $callFunc, $ackResult, $ackRawValue);
            }

            // 没有数据的时候自动退出
            if ($rPopRawValue === false) {
                while (true) {
                    $ackResult = null;
                    $ackRawValue = null;
                    $this->rPopAck($askQueueName, $callFunc, $ackResult, $ackRawValue);
                    if ($ackRawValue === false) {
                        $this->endTime = microtime(true);
                        return self::RPOP_AUTO_ACK_IS_NO_ACK_DATA;
                    }
                }
                $this->endTime = microtime(true);
                return self::RPOP_AUTO_ACK_IS_NO_DATA;
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getUseTotalSeconds()
    {
        return $this->endTime - $this->startTime;
    }
}