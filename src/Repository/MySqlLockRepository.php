<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 21:32
 */

namespace Zwei\Sync\Repository;


use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\LockRepositoryInterface;
use PDO;
use PDOStatement;

class MySqlLockRepository implements LockRepositoryInterface
{
    /**
     * @var PDO
     */
    protected $pdo;
    
    protected $table = null;
    
    /**
     * MySqlLockRepository constructor.
     * @param PDO $pdo
     * @param string $tableName
     */
    public function __construct(PDO $pdo, $tableName = 'sync_lock')
    {
        $this->pdo = $pdo;
        $this->table = $tableName;
    }
    
    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }
    
    public function getLockSql($name, $milliseconds)
    {
        $nowMilliseconds = Helper::getNowMilliseconds();
        $expiredTime = $nowMilliseconds + $milliseconds;
        $parameters = [
            ':name' => $name,
            ':expired' => $milliseconds,
            ':expired_time' => $expiredTime,
            ':now_expired_time' => $nowMilliseconds,
        ];
        $sql = <<<str
INSERT INTO {$this->table} (`name`, expired, expired_time)
  VALUES (:name, :expired, :expired_time)
ON DUPLICATE KEY UPDATE
  expired = IF(:now_expired_time > expired_time, VALUES(expired), expired),
  expired_time = IF(:now_expired_time > expired_time, VALUES(expired_time), expired_time);
str;
        return [$sql, $parameters];
    }
    
    public function getUnlockSql($name)
    {
        $milliseconds = 0;
        $nowMilliseconds = Helper::getNowMilliseconds();
        $expiredTime = $nowMilliseconds + $milliseconds - 1;
        $parameters = [
            ':name' => $name,
            ':expired' => $milliseconds,
            ':expired_time' => $expiredTime,
            ':now_expired_time' => $nowMilliseconds,
        ];
        $sql = <<<str
INSERT INTO {$this->table} (`name`, expired, expired_time)
  VALUES (:name, :expired, :expired_time)
ON DUPLICATE KEY UPDATE
  expired = IF(:now_expired_time <= expired_time, VALUES(expired), expired),
  expired_time = IF(:now_expired_time <= expired_time, VALUES(expired_time), expired_time)
str;
        return [$sql, $parameters];
    }
    
    /**
     * 加锁
     *
     * @param string $lockName
     * @param integer $milliseconds
     * @return bool
     * @throws LockParamException|LockFailException
     */
    public function lock($lockName, $milliseconds)
    {
        if (!is_numeric($milliseconds) || $milliseconds < 1) {
            throw new LockParamException("lock.param.milliseconds.error");
        }
        list($sql, $parameters) = $this->getLockSql($lockName, $milliseconds);
        $statement = $this->getPdo()->prepare($sql);
        $statement->execute($parameters);
        if ($statement->rowCount() > 0) {
            return true;
        }
        throw new LockFailException("lock.fail");
    }
    
    /**
     * 解锁
     *
     * @param string $lockName
     * @return int
     */
    public function unlock($lockName)
    {
        list($sql, $parameters) = $this->getUnlockSql($lockName);
        $statement = $this->getPdo()->prepare($sql);
        $statement->execute($parameters);
        return $statement->rowCount();
    }
}
