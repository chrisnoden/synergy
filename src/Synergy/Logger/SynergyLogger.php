<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
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
 * @package   Synergy MVC Library
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Logger;

/**
 * Class SynergyLogger
 *
 * @category Synergy\Logger
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SynergyLogger extends LoggerAbstract implements LoggerInterface
{

    /**
     * @var array
     */
    private $aLoggers = array();


    /**
     * Value of member _aLoggers
     *
     * @return array value of member
     */
    public function getLoggers()
    {
        return $this->aLoggers;
    }


    /**
     * Add a FileLogger
     *
     * @param $filename
     */
    public function addFileLogger($filename)
    {
        $this->aLoggers[] = new FileLogger($filename);
    }


    /**
     * Add a CliLogger
     */
    public function addCliLogger()
    {
        $this->aLoggers[] = new CliLogger();
    }


    /**
     * Log to each sub-logger
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     * @throw InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        /**
         * @var LoggerAbstract $logger
         */
        foreach ($this->aLoggers AS $logger)
        {
            $logger->log($level, $message, $context);
        }
    }


    /**
     * Set the value of silentConsole member
     *
     * @param boolean $silentConsole
     *
     * @return void
     */
    public function setSilentConsole($silentConsole)
    {
        foreach ($this->aLoggers AS $logger) {
            if ($logger instanceof CliLogger) {
                if ($silentConsole === true) {
                    $logger->setSilentConsole(true);
                } else {
                    $logger->setSilentConsole(false);
                }
            }
        }
    }

}