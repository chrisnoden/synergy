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
 * @category  Unit Test
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Synergy\Project\Web\SessionHandler;

use Synergy\Tests\Synergy\Project\Web\WebTestAbstract;

class MySqlSessionHandlerTest extends WebTestAbstract
{

    public function testSessionHandling()
    {
        $response = $this->fetchTestResponse('MySqlSessionHandlerTest.php');

        if (!$data = unserialize($response)) {
            $this->fail('Response from webTest: '.$response);
        }

        if (!is_array($data)) {
            $this->fail('Expected array of data from webTest');
        }

        if (!isset($data['session_id'])) {
            $this->fail('session_id not stored in response from webTest');
        } elseif (!isset($data['session_contents'])) {
            $this->fail('session_contents not stored in response from webTest');
        } elseif (!isset($data['table_name'])) {
            $this->fail('table_name not stored in response from webTest');
        }

        $table_name = $data['table_name'];
        $session_id = $data['session_id'];
        $session_contents = unserialize($data['session_contents']);

        if (!is_array($session_contents)) {
            $this->fail('session_contents does not unserialize to an array');
        }

        if (!isset($session_contents['name'])) {
            $this->fail('Expected session_contents element "name" to be set by webTest');
        }

        if (!file_exists($session_contents['name'])) {
            $this->fail('name element from session_contents doesn\'t match a file');
        }

        $time = time();
        if (!isset($session_contents['time'])) {
            $this->fail('Expected array element "time" to be set in session_contents');
        }

        if (!is_int($session_contents['time'])) {
            $this->fail('time element from session_contents is not an integer');
        }

        if ($time - $session_contents['time'] > 2) {
            $this->fail('time element from session_contents outside accepted range (session is stale)');
        }

        $pdo = new \PDO(SYNERGY_TEST_PDO_STRING, SYNERGY_TEST_DB_USERNAME, SYNERGY_TEST_DB_PASSWORD);
        $query = $pdo->prepare('SELECT * FROM `'.$table_name.'` WHERE `id`=:id');
        $query->bindParam(':id', $session_id);
        $results = $query->execute();
        if ($results === false) {
            $this->fail('Unable to find session on database');
        }
        if ($query->rowCount() != 1) {
            $this->fail('Incorrect number of results for session_id '.$session_id);
        }
        $row = $query->fetch(\PDO::FETCH_ASSOC);
        if (!isset($row['data'])) {
            $this->fail('No data column in session table');
        }
        $this->assertEquals(
            $session_contents['time'],
            $row['last_updated'],
            'expected last_updated field on db to match stored time'
        );

        // destroy the temp session table
        $sql = 'DROP TABLE IF EXISTS `' . $table_name . '`';
        $pdo->query($sql);
    }
}
