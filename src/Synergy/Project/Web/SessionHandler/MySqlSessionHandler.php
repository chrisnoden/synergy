<?php
/**
 * Created by Chris Noden using PhpStorm.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Class
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Project\Web\SessionHandler;

/**
 * Class MySqlSessionHandler
 *
 * @category Synergy\Project\Web\SessionHandler
 * @package  synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class MySqlSessionHandler extends SessionHandlerAbstract implements \SessionHandlerInterface
{

    /**
     * @var \PDO $pdo The PDO object used to access the database
     */
    private $pdo = null;
    /** @var string */
    private $table_name = 'sessions';


    /**
     * Set the value of pdo member
     *
     * @param \PDO $pdo
     *
     * @return $this
     */
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;
//        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $this;
    }


    /**
     * Set the value of table_name member
     *
     * @param string $table_name
     *
     * @return $this
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;

        return $this;
    }


    /**
     * Create the session table if it doesn't exist
     *
     * @return void
     */
    public function createSessionTable()
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (
                            `id` varchar(255) NOT NULL,
                            `last_updated` int(10) NOT NULL,
                            `data` mediumtext NOT NULL,
                            `created` int(10) NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8',
            $this->table_name
        );
        $this->pdo->query($sql);
    }

    /**
     * The open callback works like a constructor in classes and is executed when the session is being opened.
     * It is the first callback function executed when the session is started automatically or
     * manually with session_start()
     *
     * @param string $save_path The save path
     * @param string $session_name The name of the session
     *
     * @return bool true for success
     */
    public function open($save_path, $session_name)
    {
        return true;
    }


    /**
     * The close callback works like a destructor in classes and is executed after the session write
     * callback has been called. It is also invoked when session_write_close() is called
     *
     * @return bool true for success
     */
    public function close()
    {
        return true;
    }


    /**
     * The read callback must always return a session encoded (serialized) string, or an empty string
     * if there is no data to read. This callback is called internally by PHP when the session starts or
     * when session_start() is called. Before this callback is invoked PHP will invoke the open callback.
     *
     * @param string $session_id
     *
     * @return mixed
     * @throws \Exception
     */
    public function read($session_id)
    {
        // Create a query to get the session data, ...
        $select = sprintf("SELECT * FROM `%s` WHERE `id` = :id LIMIT 1;", $this->table_name);

        // ... prepare the statement, ...
        $selectStmt = $this->pdo->prepare($select);

        // ... bind the id parameter to the statement ...
        $selectStmt->bindParam(':id', $session_id, \PDO::PARAM_INT);

        // ... and try to execute the query.
        if ($selectStmt->execute()) {
            // Fetch the result as associative array ...
            $result = $selectStmt->fetch(\PDO::FETCH_ASSOC);

            // ... and validate it.
            if (isset($result['data'])) {
                return $result["data"];
            }
        }

        return '';
    }


    /**
     * Writes data into a session rather
     * into the session record in the database.
     *
     * @param string $session_id
     * @param string $data
     *
     * @return bool true on success
     */
    public function write($session_id, $data)
    {
        // Validate the given data.
        if ($data == null) {
            return true;
        }

        // Setup the query to update a session, ...
        $update = sprintf("UPDATE `%s` SET `last_updated` = :time, `data` = :data WHERE `id` = :id", $this->table_name);

        // ... prepare the statement, ...
        $updateStmt = $this->pdo->prepare($update);

        // ... bind the parameters to the statement ...
        $time = time();
        $updateStmt->bindParam(':time', $time, \PDO::PARAM_INT);
        $updateStmt->bindParam(':data', $data, \PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $session_id, \PDO::PARAM_INT);

        // ... and try to execute the query.
        if ($updateStmt->execute()) {
            // Check if any data set was updated.
            if ($updateStmt->rowCount() > 0) {
                return true;
            } else {
                // The session does not exists create a new one, ...
                $insert = sprintf("INSERT INTO `%s` (`id`, `last_updated`, `created`, `data`) VALUES (:id, :time, :time, :data)", $this->table_name);

                // ... prepare the statement, ...
                $insertStmt = $this->pdo->prepare($insert);

                // ... bind the parameters to the statement ...
                $time = time();
                $insertStmt->bindParam(':time', $time, \PDO::PARAM_INT);
                $insertStmt->bindParam(':data', $data, \PDO::PARAM_STR);
                $insertStmt->bindParam(':id', $session_id, \PDO::PARAM_INT);

                // .. and finally execute it.
                if (!$insertStmt->execute()) {
                    return false;
                }
                return true;
            }
        }

        return false;
    }


    /**
     * This callback is executed when a session is destroyed with session_destroy() or with
     * session_regenerate_id() with the destroy parameter set to TRUE
     *
     * @param string $session_id
     *
     * @return bool true on success
     */
    public function destroy($session_id)
    {
        // Setup a query to delete the current session, ...
        $delete = sprintf("DELETE FROM `%s` WHERE `id` = :id LIMIT 1", $this->table_name);

        // ... prepare the statement, ...
        $deleteStmt = $this->pdo->prepare($delete);

        // ... bind the parameters to the statement ...
        $deleteStmt->bindParam(':id', $session_id, \PDO::PARAM_INT);

        // ... and execute the query.
        return $deleteStmt->execute();
    }


    /**
     * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
     * The frequency is controlled by session.gc_probability and session.gc_divisor. The value of lifetime
     * which is passed to this callback can be set in session.gc_maxlifetime.
     *
     * @param string $maxlifetime
     *
     * @return bool true on success
     */
    public function gc($maxlifetime)
    {
        // Set a period after that a session pass off.
        $maxlifetime = strtotime("-20 minutes");

        // Setup a query to delete discontinued sessions, ...
        $delete = sprintf("DELETE FROM `%s` WHERE `last_updated` < :maxlifetime", $this->table_name);

        // ... prepare the statement, ...
        $deleteStmt = $this->pdo->prepare($delete);

        // ... bind the parameters to the statement ...
        $deleteStmt->bindParam(':maxlifetime', $maxlifetime, \PDO::PARAM_INT);

        // ... and execute the query.
        return $deleteStmt->execute();
    }
}
