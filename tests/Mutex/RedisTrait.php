<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:52
 */

namespace Zwei\Sync\Tests\Mutex;


trait RedisTrait
{
    public function getRedis()
    {
        $host = '199.199.199.199';
        $post = 16379;
        $password = '000000';
        $redis = new \Redis();
        $redis->connect($host, $post);
        $redis->auth($password);
        return $redis;
    }
}
