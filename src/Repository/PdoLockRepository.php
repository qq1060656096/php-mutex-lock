<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 21:32
 */

namespace Zwei\Sync\Repository;


use Zwei\Sync\Exception\LockFailException;
use Zwei\Sync\Exception\UnLockTimeoutException;
use Zwei\Sync\Helper\Helper;
use Zwei\Sync\LockRepositoryInterface;
use PDO;
use PDOStatement;

class PdoLockRepository implements LockRepositoryInterface
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
     * @inheritdoc
     */
    public function lock($clientId, $expired, ...$lockNames)
    {
        Helper::validateLockExpired($expired);
        Helper::validateLockNames(...$lockNames);
        $nowMilliseconds = Helper::getNowMilliseconds();
        
        // 删除过期锁
        $namesPlaceholder = array_pad([], count($lockNames), '?');
        $deleteExpiredLockSql = sprintf("delete from %s where `name` in(%s) and expired_time <= %s", $this->table, implode(',', $namesPlaceholder), $nowMilliseconds);
        $statement = $this->getPdo()->prepare($deleteExpiredLockSql);
        $index = 0;
        foreach ($lockNames as $name) {
            $tmpName  = $name;
            $index ++;
            $statement->bindValue($index, $tmpName);
        }
        $statement->execute();
    
    
        $insertArr = [];
        $insertArr[] = sprintf("INSERT INTO %s (`name`, expired, expired_time, client_id)", $this->table);
        $insertValuesArr = [];
        $pdoBindParams = [];
        foreach ($lockNames as $name) {
            $insertValuesArr[] = "(?, ?, ?, ?)";
            $pdoBindParams[] = $name;
            $pdoBindParams[] = $expired;
            $pdoBindParams[] = $nowMilliseconds + $expired;
            $pdoBindParams[] = $clientId;
        }
        $insertArr[] = "VALUES";
        $insertArr[] = implode(",", $insertValuesArr);
        $sql = implode("", $insertArr);
        $statement = $this->getPdo()->prepare($sql);
        $statement->execute($pdoBindParams);
        $count = $statement->rowCount();
        if ($count > 0) {
            return $count;
        }
        LockFailException::fail();
    }
    
    /**
     * 解锁
     *
     * @inheritdoc
     */
    public function unlock($clientId, ...$lockNames)
    {
        Helper::validateLockNames(...$lockNames);
        $nowMilliseconds = Helper::getNowMilliseconds();
        $namesPlaceholder = array_pad([], count($lockNames), '?');
        // 删除过期锁
        $deleteExpiredLockSql = sprintf("delete from %s where `name` in(%s) and client_id = ? and expired_time >= %s", $this->table, implode(',', $namesPlaceholder), $nowMilliseconds);
        $statement = $this->getPdo()->prepare($deleteExpiredLockSql);
        $index = 0;
        foreach ($lockNames as $name) {
            $tmpName  = $name;
            $index ++;
            $statement->bindValue($index, $tmpName);
        }
        $statement->bindValue($index + 1, $clientId);
    
        $statement->execute();
        $count = $statement->rowCount();
        if ($count < 1) {
            UnLockTimeoutException::timeout();
        }
        return ;
    }
}
