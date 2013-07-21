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

use Synergy\Project\Cli\ArgumentParser;

/**
 * Class CliLogger
 * a simple fall-back logger to output to the CLI for daemon and console
 * projects
 *
 * @category Synergy\Logger
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class CliLogger extends LoggerAbstract implements LoggerInterface
{

    /**
     * @var bool
     */
    private $_silent = false;
    /**
     * @var int
     */
    private $_verbosity = 0;


    /**
     * Create a new FileLogger object
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();

        if (PHP_SAPI == 'cli') {
            $arg = ArgumentParser::parseArguments();
            if ($arg->hasSwitch('v')) {
                $this->_verbosity = 1;
            } else if ($arg->hasSwitch('vv')) {
                $this->_verbosity = 2;
            }
        }
    }


    /**
     * Logs to the File
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     * @throw InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);

        if ($this->isValidLogLevel($level)) {
            if ($this->_silent === true) {
                switch ($level) {
                    case LogLevel::EMERGENCY:
                    case LogLevel::CRITICAL:
                        \Cli\err(sprintf(
                            "%s %s",
                            $level,
                            $message
                        ));
                        break;
                }
            }  else {
                switch ($level) {
                    case LogLevel::EMERGENCY:
                    case LogLevel::CRITICAL:
                        \Cli\err(sprintf(
                            "%%R%11s%%n %s",
                            $level,
                            $message
                        ));
                        break;
                    case LogLevel::ERROR:
                    case LogLevel::ALERT:
                        \Cli\err(sprintf(
                            "%%r%11s%%n %s",
                            $level,
                            $message
                        ));
                        break;
                    case LogLevel::WARNING:
                    case LogLevel::NOTICE:
                        \Cli\err(sprintf(
                            "%%c%11s%%n %s",
                            $level,
                            $message
                        ));
                        break;
                    case LogLevel::INFO:
                        if ($this->_verbosity >= 1) {
                            \Cli\err(sprintf(
                                "%%y%11s%%n %s",
                                $level,
                                $message
                            ));
                        }
                        break;

                    default:
                        if ($this->_verbosity >= 2) {
                            \Cli\line(sprintf(
                                "%%n%11s %s",
                                $level,
                                $message
                            ));
                        }
                }
            }
        }

    }


    /**
     * Silence any stdout or normal console output
     *
     * @param bool $silentConsole silence the console
     *
     * @return void
     */
    public function setSilentConsole($silentConsole)
    {
        if ($silentConsole === true) {
            $this->_silent = true;
        } else {
            $this->_silent = false;
        }
    }

}