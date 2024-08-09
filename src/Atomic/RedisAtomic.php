<?php

namespace Zwei\Sync\Atomic;

class RedisAtomic {
    /**
     * @var \Redis
     */
    protected $redis;

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
     * @param null|mixed $ackValue
     * @return null
     * @throws \RedisException
     */
    public function rPopAck($askQueueName, callable $callFunc, &$ackResult = null, &$ackValue = null)
    {
        $result = null;
        $value = $this->getRedis()->lIndex($askQueueName, -1);
        $ackValue = $value;
        if ($value !== false) {
            $result = $callFunc($value);
            // 没有删除的数据，表示没有消费成功。下次执行的时候还会继续消费，直到删除成功
            $ackResult = $this->getRedis()->lRem($askQueueName, $value, -1);
        }
        return $result;
    }

    /**
     * rPop 自动ack
     *
     * @param bool $rPopNoValueForceExit
     * @param string $queueName
     * @param string $askQueueName
     * @param callable $callFunc
     * @return void
     * @throws \RedisException
     */
    public function rPopAutoAck($rPopNoValueForceExit, $queueName, $askQueueName, callable $callFunc) {
        $count = 0;
        $ackCount = 1000;// 多久处理一次ack队列异常数据
        while(true) {
            $count ++;
            $ackResult = null;
            $rPopRawValue = null;
            $this->rPop($queueName, $askQueueName, function ($data) use ($callFunc) {
                $callFunc($data);
            }, $ackResult, $rPopRawValue);
            if ($rPopRawValue === false && $rPopNoValueForceExit) {
                // 退出前也处理一次ack队列异常数据
                $this->rPopAck($askQueueName, $callFunc);
                exit("rPopNoValueExit");
            }
            // 每执行多少次，就处理一次ack队列异常数据
            if ($count % $ackCount === 0) {
                $this->rPopAck($askQueueName, $callFunc);
            }
        }
    }
}