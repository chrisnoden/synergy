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
 * @package   Synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */
declare(ticks = 1);

namespace Synergy\Project\Daemon;

use Synergy\Logger\Logger;
use Synergy\Project\Cli\CliProject;
use Synergy\Project\Cli\SignalHandler;

/**
 * Class DaemonProject
 *
 * @category Synergy\Project\Daemon
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class DaemonProject extends CliProject
{

    /**
     * @var int
     */
    protected $process_pid;
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


    public function __destruct()
    {
        if (isset($this->process_pid)) {
            Logger::alert(
                'Daemon process '.getmypid().' ended'
            );
        } else {
            Logger::debug(
                'parent process '.getmypid().' ended'
            );
        }

        parent::__destruct();
    }

    /**
     * run our project
     *
     * @return void
     */
    public function launch()
    {
        $this->fork_to_bg();

        parent::launch();
    }


    /**
     * Daemonises the process
     */
    protected function fork_to_bg() {
        $daemon_pid = pcntl_fork();
        switch ($daemon_pid) {
            case -1:
                Logger::emergency(
                    'Unable to fork daemon process'
                );
                exit(1); // fork failed
            case 0:
                // this child is our daemon
                $this->process_pid = getmypid();
                break;
            default:
                // we are the parent - the one from the FG command line
                // return control to command line by exiting...
                Logger::info(
                    'Daemon process running : pid='.$daemon_pid
                );
                exit(0);
        }

        // promote the daemon process so it doesn't die because the parent has
        if (posix_setsid() === -1) {
            Logger::critical(
                'Error creating daemon process'
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

        Logger::notice(
            'Daemon process running : pid='.getmypid()
        );
    }


}