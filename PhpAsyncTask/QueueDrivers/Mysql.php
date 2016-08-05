<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/19/16
 * Time: 21:58
 */

namespace Adocwang\Pat\QueueDrivers;


use PDO;

class Mysql implements QueueDriverInterface
{
    private static $connectionInstance;

    private $server;

    private $port = 3306;
    private $user;
    private $password;
    private $dbName;
    private $lastData = array();

    public function __construct($config)
    {
        $this->server = $config['host'];
//        print_r($config);
        if (!empty($config['port'])) {
            $this->port = $config['port'];
        }
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->dbName = $config['db'];
        if (empty(self::$connectionInstance)) {
            $this->initConnection();
        }
    }

    public function initConnection()
    {
        try {
            self::$connectionInstance = new PDO("mysql:host=" . $this->server . ";dbname=" . $this->dbName, $this->user, $this->password, array(PDO::ATTR_PERSISTENT => true));
            $tableExistsSql = 'SHOW TABLES LIKE \'php_async_task_queue\';';
            $existNum = self::$connectionInstance->query($tableExistsSql)->rowCount();
//        print_r($existNum);
            if ($existNum != 1) {
                $createTableSql = <<<EOF
CREATE TABLE `php_async_task_queue` (
  `id` int(11) NOT NULL,
  `task_key` varchar(63) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

ALTER TABLE `php_async_task_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_key_id` (`task_key`,`id`);

ALTER TABLE `php_async_task_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
EOF;
                self::$connectionInstance->query($createTableSql);
            }
        } catch (\PDOException $e) {
            throw new MQException($e->getMessage());
        }
    }

    public function getConnectionInstance()
    {
        if (empty(self::$connectionInstance)) {
            $this->initConnection();
        }
        return self::$connectionInstance;
    }

    /**
     * get top data of queue
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function pop($key)
    {
        //TODO need transaction here
        $sql = "SELECT `id`,`task_key`,`value` FROM `php_async_task_queue` WHERE `task_key`='" . $key . "' ORDER BY `id` ASC LIMIT 1";
//        echo $sql;
        $row = $this->getConnectionInstance()->query($sql)->fetch(PDO::FETCH_ASSOC);
        $data = null;
        if (!empty($row['value'])) {
            $this->deleteRow($row['id']);
            $data = $row['value'];
        }
        $this->lastData[$key] = $data;
        return $data;
    }

    private function deleteRow($id)
    {
        $deleteSql = "DELETE FROM `php_async_task_queue` WHERE `id`='" . $id . "';";
        $this->getConnectionInstance()->query($deleteSql);
    }

    /**
     * get top data of queue,if there is no data in queue,this function will block
     *
     * @param $key string name of queue
     * @return mixed
     */
    public function blPop($key)
    {
        return $this->pop($key);
    }

    /**
     * put data to the bottom of queue
     *
     * @param $key string name of queue
     * @param $data string
     * @return boolean
     */
    public function push(string $key, string $data)
    {
        $sql = "INSERT INTO `php_async_task_queue` (`task_key`,`value`) VALUES('" . addslashes($key) . "', '" . addslashes($data) . "')";
        $this->getConnectionInstance()->query($sql);
        return $this->getConnectionInstance()->lastinsertid();
    }

    /**
     * count queue's length
     *
     * @param $key string name of queue
     * @return int
     */
    public function count($key)
    {
        $sql = "SELECT COUNT(*) AS `count` FROM `php_async_task_queue` WHERE `task_key` = '" . $key . "';";
//        echo $sql;
        $row = $this->getConnectionInstance()->query($sql)->fetch(PDO::FETCH_ASSOC);
//        print_r($this->getConnectionInstance()->errorInfo());
        if (!empty($row['count'])) {
            return $row['count'];
        }
        return 0;
    }

    /**
     * clean all data in queue
     *
     * @param $key string name of queue
     * @return boolean
     */
    public function clear($key)
    {
        $sql = "DELETE FROM `php_async_task_queue` WHERE `task_key`='" . $key . "'";
        $this->getConnectionInstance()->query($sql);
        return true;
    }

    public function revert($key)
    {
        $result = false;
        if (!empty($this->lastData[$key])) {
            $sql = "INSERT INTO `php_async_task_queue` (`id`,`task_key`,`value`) VALUES('". $this->lastData[$key]['id']."','" . addslashes($this->lastData[$key]['task_key']) . "', '" . addslashes($this->lastData[$key]['value']) . "')";
            $result = $this->getConnectionInstance()->query($sql);
            unset($this->lastData[$key]);
        }
        return $result;
    }
}