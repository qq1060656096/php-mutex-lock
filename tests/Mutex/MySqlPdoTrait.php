<?php
/**
 * Created by PhpStorm.
 * User: zhaoweijie
 * Date: 2020-04-24
 * Time: 15:52
 */

namespace Zwei\Sync\Tests\Mutex;


trait MySqlPdoTrait
{
    public function getPdo()
    {
        $host = '199.199.199.199';
        $post = 13306;
        $dbName = 'sync';
        $user = 'root';
        $pass = 'root';
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $post, $dbName);
        $pdo = new \PDO($dsn, $user, $pass);
        return $pdo;
    }
}
