<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2024-08-09
 * Time: 10:39
 */

namespace Zwei\Sync\Tests\Atomic;

use PHPUnit\Framework\TestCase;
use Zwei\Sync\Atomic\RedisAtomic;

class RedisAtomicTest extends TestCase
{
    public function getRedis()
    {
        $redis = new \Redis();
//        $result = $redis->connect('172.18.176.1', 6379);
        $result = $redis->connect('172.29.112.1', 6379);
        return $redis;
    }

    /**
     * @return void
     * @throws \RedisException
     */
    public function testRPop()
    {
        $queueName = "q1";
        $queueAckName = "q1_ack";

        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueName, "v1");
        $redisAtomic->getRedis()->lPush($queueName, "v2");
        $redisAtomic->getRedis()->lPush($queueName, "v3");
        $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueName));
        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });
        $this->assertEquals('v1', $result);
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });
        $this->assertEquals('v2', $result);
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });
        $this->assertEquals('v3', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));
    }

    public function testRPopException() {
        $queueName = "q1";
        $queueAckName = "q1_ack";
        $result = null;
        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueName, "v1");
        $redisAtomic->getRedis()->lPush($queueName, "v2");
        $redisAtomic->getRedis()->lPush($queueName, "v3");
        $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueName));
        try {
            $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
                $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
                throw new \Exception("test RedisAtomic->rPop exception");
                return $data;
            });
        } catch (\Exception $exception) {
        }

        $this->assertEquals(null, $result);
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));

        try {
            $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
                $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
                throw new \Exception("test RedisAtomic->rPop exception");
                return $data;
            });
        } catch (\Exception $exception) {
        }
        $this->assertEquals(null, $result);
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });
        $this->assertEquals('v3', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v1', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v2', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));
    }

    public function testRPopAck() {
        $queueName = "q1";
        $queueAckName = "q1_ack";
        $result = null;
        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueName, "v1");
        $redisAtomic->getRedis()->lPush($queueName, "v2");
        $redisAtomic->getRedis()->lPush($queueName, "v3");
        $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueName));
        try {
            $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
                $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
                throw new \Exception("test RedisAtomic->rPop exception");
                return $data;
            });
        } catch (\Exception $exception) {
        }

        try {
            $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
                $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
                throw new \Exception("test RedisAtomic->rPop exception");
                return $data;
            });
        } catch (\Exception $exception) {
        }
        $this->assertEquals(null, $result);
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });
        $this->assertEquals('v3', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v1', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v2', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));
    }


    public function testRPopAckQueueHasData() {
        $queueName = "q1";
        $queueAckName = "q1_ack";

        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueAckName, "v1");
        $redisAtomic->getRedis()->lPush($queueAckName, "v2");
        $redisAtomic->getRedis()->lPush($queueAckName, "v3");
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPop($queueName, $queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            throw new \Exception("test RedisAtomic->rPop exception");
            return $data;
        });
        $this->assertEquals(null, $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(3, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v1', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(2, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v2', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));

        $result = $redisAtomic->rPopAck($queueAckName, function ($data) use ($redisAtomic, $queueAckName) {
            $this->assertEquals(1, $redisAtomic->getRedis()->lLen($queueAckName));
            return $data;
        });

        $this->assertEquals('v3', $result);
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueName));
        $this->assertEquals(0, $redisAtomic->getRedis()->lLen($queueAckName));
    }

    public function testExampleNoData() {
        $queueName = "q1";
        $queueAckName = "q1_ack";

        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueName, "v1");
        $redisAtomic->getRedis()->lPush($queueName, "v2");
        $redisAtomic->getRedis()->lPush($queueName, "v3");
        $redisAtomic->getRedis()->lPush($queueName, "v4");

        $redisAtomic->rPopAutoAck(1000, $queueName, $queueAckName, function ($data) {
            var_dump($data);
//            throw new \Exception($data);
            sleep(1);
        });
        $useTotalSeconds =$redisAtomic->getUseTotalSeconds();
        $this->assertEquals(4, floor($useTotalSeconds));
    }

    public function testExampleNoAckData() {
        $queueName = "q1";
        $queueAckName = "q1_ack";

        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v1");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v2");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v3");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v4");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v5");

        $redisAtomic->rPopAutoAck(1000, $queueName, $queueAckName, function ($data) {
            var_dump($data);
//            throw new \Exception($data);
            sleep(1);
        });
        $useTotalSeconds =$redisAtomic->getUseTotalSeconds();
        $this->assertEquals(5, floor($useTotalSeconds));
    }

    public function testExampleAll() {
        $queueName = "q1";
        $queueAckName = "q1_ack";

        $redisAtomic = new RedisAtomic($this->getRedis());
        $redisAtomic->getRedis()->del($queueName, $queueAckName);
        $redisAtomic->getRedis()->lPush($queueName, "v1");
        $redisAtomic->getRedis()->lPush($queueName, "v2");
        $redisAtomic->getRedis()->lPush($queueName, "v3");
        $redisAtomic->getRedis()->lPush($queueName, "v4");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v1");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v2");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v3");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v4");
        $redisAtomic->getRedis()->lPush($queueAckName, "ack_v5");
        $redisAtomic->rPopAutoAck(2, $queueName, $queueAckName, function ($data) {
            var_dump($data);
            sleep(1);
        });
        $useTotalSeconds =$redisAtomic->getUseTotalSeconds();
        $this->assertEquals(9, floor($useTotalSeconds));
    }
}
