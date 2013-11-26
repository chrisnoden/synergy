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
 * @category  File
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php')) {
    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php');

    $pdo        = new PDO(SYNERGY_TEST_PDO_STRING, SYNERGY_TEST_DB_USERNAME, SYNERGY_TEST_DB_PASSWORD);
    $table_name = 'test_sessions';

    $sql = 'DROP TABLE IF EXISTS `' . $table_name . '`';
    $pdo->query($sql);

    $sql = sprintf(
        'CREATE TABLE `%s` (
                        `id` varchar(255) NOT NULL,
                        `last_updated` int(10) NOT NULL,
                        `data` mediumtext NOT NULL,
                        `created` int(10) NOT NULL,
                        PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8',
        $table_name
    );
    $pdo->query($sql);

    // Initialist the session handler
    $sh               = new \Synergy\Project\Web\SessionHandler\MySqlSessionHandler($pdo, $table_name);
    $_SESSION['name'] = __FILE__;
    $_SESSION['time'] = time();

    $response = array(
        'session_id'       => session_id(),
        'table_name'       => $table_name,
        'session_contents' => serialize($_SESSION)
    );

    printf('%s', serialize($response));
}
