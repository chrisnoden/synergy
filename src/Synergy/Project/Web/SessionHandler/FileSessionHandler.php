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
 * Class FileSessionHandler
 *
 * @category Synergy\Project\Web\SessionHandler
 * @package  synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class FileSessionHandler extends SessionHandlerAbstract implements \SessionHandlerInterface
{

    /** @var string */
    private $savePath;
    /** @var string */
    private $sess_prefix = 'synergy_sess_';


    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0770);
        }

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        return (string)@file_get_contents(sprintf("%s/%s%s", $this->savePath, $this->sess_prefix, $session_id));
    }


    /**
     * {@inheritdoc}
     */
    public function write($session_id, $data)
    {
        return file_put_contents(
            sprintf("%s/%s%s", $this->savePath, $this->sess_prefix, $session_id),
            $data
        ) === false ? false : true;
    }


    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        $file = sprintf("%s/%s%s", $this->savePath, $this->sess_prefix, $session_id);
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        foreach (glob(sprintf("%s/%s*", $this->savePath, $this->sess_prefix)) as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
