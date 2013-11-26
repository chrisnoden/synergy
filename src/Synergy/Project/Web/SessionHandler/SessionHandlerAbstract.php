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
 * Class SessionHandlerAbstract
 *
 * @category Synergy\Project\Web\SessionHandler
 * @package  synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
abstract class SessionHandlerAbstract
{

    /**
     * Auto register the session handler
     */
    public function __construct()
    {
        // change the ini configuration
        ini_set('session.save_handler', 'user');

        // ... set the session handler to the class methods ...
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

        // Finally ensure that the session values are stored.
        register_shutdown_function('session_write_close');
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
    abstract public function open($save_path, $session_name);


    /**
     * The close callback works like a destructor in classes and is executed after the session write
     * callback has been called. It is also invoked when session_write_close() is called
     *
     * @return bool true for success
     */
    abstract public function close();


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
    abstract public function read($session_id);


    /**
     * Writes data into a session rather
     * into the session record in the database.
     *
     * @param string $session_id
     * @param string $data
     *
     * @return bool true on success
     */
    abstract public function write($session_id, $data);


    /**
     * This callback is executed when a session is destroyed with session_destroy() or with
     * session_regenerate_id() with the destroy parameter set to TRUE
     *
     * @param string $session_id
     *
     * @return bool true on success
     */
    abstract public function destroy($session_id);


    /**
     * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
     * The frequency is controlled by session.gc_probability and session.gc_divisor. The value of lifetime
     * which is passed to this callback can be set in session.gc_maxlifetime.
     *
     * @param string $maxlifetime
     *
     * @return bool true on success
     */
    abstract public function gc($maxlifetime);
}
