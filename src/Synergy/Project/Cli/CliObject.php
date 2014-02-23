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
use Synergy\Project;

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
    private $stdIn;
    /**
     * @var resource
     */
    private $stdOut;
    /**
     * @var resource
     */
    private $stdErr;
    /**
     * @var int what position are we in the process hierarchy
     */
    private $node = 0;
    /**
     * @var array
     */
    private $pid = array();
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
        $this->pid[$this->node] = getmypid();
    }


    public function __destruct()
    {
        if ($this->node == 0) {
            foreach ($this->pid AS $child_pid) {
                if ($child_pid == getmypid()) {
                    continue;
                }
                Logger::notice(
                    sprintf(
                        'TERM signal sent to child pid: %s',
                        $child_pid
                    )
                );
                posix_kill($child_pid, SIGTERM);
            }
        }

        if (isset($this->projectName)) {
            Logger::notice(
                sprintf(
                    '%s : PROJECT TERMINATED',
                    $this->projectName
                )
            );
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
        $handle = fopen("php://stdin", "r");
        $line   = fgets($handle);
        fclose($handle);
        return trim($line);
    }


    /**
     * Fork the process into two
     * You can create an optional method called preFork() which is called before the fork
     * Also an optional method called postFork() which is called after the fork on any remaining processes
     * (ie if you choose to daemonize then the original foreground process will not call the postFork() method)
     *
     * @param bool $daemonize kill the parent (original) process and let the new child run
     *
     * @return void
     */
    protected function fork($daemonize = false)
    {
        if ($this->node !== 0) {
            return false;
        }

        // call any user created preFork method
        if (method_exists($this, 'preFork')) {
            $this->preFork();
        }

        // force Propel to close connections so that it will reconnect on next query
        if (class_exists('\Propel')) {
            \Propel::close();
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
                $this->node++;
                break;
            default:
                // we are the original process
                if ($this->node == 0) {
                    \Cli\line(
                        'Child node : pid=%y%s%n',
                        $pid
                    );
                    if ($daemonize) {
//                        exit;
                    } else {
                        $this->pid[] = $pid;
                    }
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

        $this->stdIn  = fopen('/dev/null', 'r'); // set fd/0
        $this->stdOut = fopen('/dev/null', 'w'); // set fd/1
        $this->stdErr = fopen('php://stdout', 'w'); // a hack to duplicate fd/1 to 2

        // Silence any console output from the logger
        Logger::setSilentConsole(true);

        // call any user created postFork method
        if (method_exists($this, 'postFork')) {
            $this->postFork();
        }
    }


    /**
     * Exits this process if there is already one running (this one makes 2)
     *
     * @return void
     */
    protected function thereCanBeOnlyOne()
    {
        $controller = $this->__toString();
        $parts      = preg_split('/[^a-zA-Z0-9]{1,}/', $controller);
        $regex      = join('[^a-zA-Z0-9]{1,2}', $parts);

        $cmd = 'ps ax | grep -v grep | egrep -c "' . $regex . '"';
        $res = `$cmd`;
        if (intval($res) > 1) {
            Logger::critical(
                'Unable to launch : Process already running'
            );
            exit(1);
        }
    }


    /**
     * Is this the parent (original) process
     *
     * @return bool
     */
    protected function isParent()
    {
        if ($this->node == 0) {
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