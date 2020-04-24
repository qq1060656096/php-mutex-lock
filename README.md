# php-sync
php 互斥锁

| 锁类型 | 堵塞类型 | 分布式 | 锁时间 |
| :------- | :----  | :----  | :----  |
| 互斥锁(Mutex)    | 堵塞   | 支持 | 毫秒级 |
| 业务互斥锁(BusinessMutex)  |堵塞   | 支持 | 毫秒级 |
| 示例订单业务互斥锁(OrderBusinessMutex) | 非堵塞  | 支持 | 毫秒级 |

| 锁仓储类型 | 是否支持 |
| :------- | :----  |
| Redis    | 支持   |
| Mysql    | 待开发  |

## 安装方式1
> 创建composer.json文件,并写入以下内容:

```php
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/qq1060656096/php-sync.git"
        }
    ],
    "require": {
        "zwei/php-sync": "0.0.1"
    }
}
```
> 执行composer install





### 业务锁示例1
```php
<?php
use Zwei\Sync\Mutex\OrderBusinessMutex;
try {
    $orderBusinessMutex = new OrderBusinessMutex();
    $orderBusinessMutex->synchronized(function(){
        // todo
    });
} catch (LockFailException $exception) {
    // 其他人正在操作, 请稍后在试
} catch (LockTimeoutException $exception) {
    // 加锁超时
} catch (UnLockTimeoutException $exception) {
    // 解锁超时
}
```

### 业务锁示例2
```php
<?php
use Zwei\Sync\Mutex\OrderBusinessMutex;

try {
    $orderBusinessMutex = new OrderBusinessMutex();
    $orderBusinessMutex->lock();
    // todo
    $orderBusinessMutex->unlock();
} catch (LockFailException $exception) {
    // 其他人正在操作, 请稍后在试
} catch (LockTimeoutException $exception) {
    // 加锁超时
} catch (UnLockTimeoutException $exception) {
    // 解锁超时
}
```


### 单元测试使用
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests

```
redis-cli -h 199.199.199.199 -p 16379 -a 000000

# 测试业务互斥锁(非堵塞):20秒内多次加锁会失败
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testLock

# 测试业务互斥锁(非堵塞):20秒内多次加锁会异常
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testLockFail

# 测试业务互斥锁(非堵塞):加锁解锁
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testLockAndUnlock

# 测试业务互斥锁(非堵塞):闭包调用
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testSynchronized

# 测试互斥锁(非堵塞):解锁超时
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testSynchronizedUnlockTimeOut
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testUnlockTimeout

# 测试互斥锁(非堵塞):未加时,解锁异常
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/BusinessMutexTest.php --filter=testUnlockNoLockUnLockFailException

# 测试互斥锁(堵塞):20秒内多次加锁会堵塞
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/MutexTest.php --filter=testLock

# 测试互斥锁(堵塞):加锁解锁
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/MutexTest.php --filter=testLockAndUnlock

# 测试互斥锁(堵塞):闭包调用
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/MutexTest.php --filter=testSynchronized


# 测试互斥锁(堵塞):解锁超时
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/MutexTest.php --filter=testSynchronizedUnlockTimeOut
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/MutexTest.php --filter=testUnlockTimeout

```

### 订单业务锁
```
# 测试互斥锁(堵塞):解锁超时
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/OrderBusinessMutexTest.php --filter=testSynchronizedUnlockTimeOut
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/Mutex/OrderBusinessMutexTest.php --filter=testUnlockTimeout

```
