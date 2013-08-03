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

namespace Synergy\Project\Cli;

use Synergy\Exception\SynergyException;
use Synergy\Logger\Logger;
use Synergy\Object;

/**
 * Class CliObject
 * Extend from this class if you are building a Cli/Console/Daemon project class
 * and want to exploit some advanced functionality
 *
 * @category Synergy\Project\Cli
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class CliObject extends Object
{
    /**
     * @var resource
     */
    private $_stdIn;
    /**
     * @var resource
     */
    private $_stdOut;
    /**
     * @var resource
     */
    private $_stdErr;
    /**
     * @var int what position are we in the process hierarchy
     */
    private $_node = 0;
    /**
     * @var array
     */
    private $_pid = array();
    /**
     * @var string
     */
    protected $projectName;


    public function __construct()
    {
        // Check this is coming from the CLI
        if (PHP_SAPI !== 'cli') {
            throw new SynergyException(
                sprintf('%s must be run from command line project', __CLASS__)
            );
        }
        // save the pid of this process
        $this->_pid[$this->_node] = getmypid();
    }


    public function __destruct()
    {
        if ($this->_node == 0) {
            foreach ($this->_pid AS $child_pid) {
                if ($child_pid == getmypid()) {
                    continue;
                }
                Logger::notice(sprintf(
                    'TERM signal sent to child pid: %s',
                    $child_pid
                ));
                posix_kill($child_pid, SIGTERM);
            }
        }

        if (isset($this->projectName)) {
            Logger::notice(sprintf(
                '%s : PROJECT TERMINATED',
                $this->projectName
            ));
        } else {
            Logger::notice('TERMINATED');
        }
    }


    /**
     * waits for input from the user (terminated by a carriage-return / newline)
     *
     * @param string $prompt text to prompt on the console
     *
     * @return string the text inputted on the command line
     */
    protected function getInput($prompt = '>')
    {
        \cli\out('%s: ', $prompt);
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        return trim($line);
    }


    /**
     * Fork the process into two
     *
     * @param bool $daemonize kill the parent (original) process and let the new child run
     *
     * @return void
     */
    protected function fork($daemonize = false) {
        if ($this->_node !== 0) {
            return false;
        }
        $pid = pcntl_fork();
        switch ($pid) {
            case -1:
                Logger::emergency(
                    'Unable to fork process'
                );
                return;
            case 0:
                // this is the child
                $this->_node++;
                break;
            default:
                // we are the original process
                if ($this->_node == 0) {
                    \Cli\line(
                        'Child node : pid=%y%s%n', $pid
                    );
                    if ($daemonize) {
                        exit;
                    }
                    $this->_pid[] = $pid;
                    return;
                }
        }

        // promote the daemon process so it doesn't die because the parent has
        if ($daemonize && posix_setsid() === -1) {
            Logger::critical(
                'Error creating daemon as session leader'
            );
            exit(1);
        }

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        $this->_stdIn  = fopen('/dev/null', 'r'); // set fd/0
        $this->_stdOut = fopen('/dev/null', 'w'); // set fd/1
        $this->_stdErr = fopen('php://stdout', 'w'); // a hack to duplicate fd/1 to 2

        // Silence any console output from the logger
        Logger::setSilentConsole(true);
    }


    /**
     * Is this the parent (original) process
     *
     * @return bool
     */
    protected function isParent()
    {
        if ($this->_node == 0) {
            return true;
        }
    }


    /**
     * Set the value of projectName member
     *
     * @param string $projectName
     *
     * @return void
     */
    protected function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }


    /**
     * Value of member projectName
     *
     * @return string value of member
     */
    protected function getProjectName()
    {
        return $this->projectName;
    }

}