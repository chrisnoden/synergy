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

class FileSessionHandlerTest extends WebTestAbstract
{

    public function testSessionHandling()
    {
        $response = $this->fetchTestResponse('FileSessionHandlerTest.php');

        if (!$data = unserialize($response)) {
            $this->fail('Response from webTest: '.$response);
        }

        if (!is_array($data)) {
            $this->fail('Expected array of session data from webTest');
        }

        if (!isset($data['name'])) {
            $this->fail('Expected array element "name" to be set by webTest');
        }

        if (!file_exists($data['name'])) {
            $this->fail('name element from webTest doesn\'t match a file');
        }

        $time = time();
        if (!isset($data['time'])) {
            $this->fail('Expected array element "time" to be set by webTest');
        }

        if (!is_int($data['time'])) {
            $this->fail('time element from webTest is not an integer');
        }

        if ($time - $data['time'] > 2) {
            $this->fail('time element from webTest outside accepted range (session is stale)');
        }
    }
}
