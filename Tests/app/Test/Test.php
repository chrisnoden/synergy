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

namespace Test;

use Synergy\Logger\Logger;
use Synergy\Project\Cli\CliObject;
use Synergy\Project\Cli\SignalHandler;
use Synergy\Tools\Tools;

/**
 * Class Test
 *
 * @category Test
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Test extends CliObject
{

    public function defaultAction()
    {
        $this->fork(true); // daemonize

        if ($this->isParent()) {
            Logger::debug('Test Log 1');
            Logger::info('Test Log 2');
            Logger::notice('Test Log 3');
            Logger::warning('Test Log 4');
        } else {
            Logger::error('Test Log 5');
            Logger::critical('Test Log 6');
            Logger::alert('Test Log 7');
            Logger::emergency('Test Log 8');
        }
        $count = 1;
        do
        {
            SignalHandler::$blockExit = true;
            Tools::pause(5);
            if ($this->isParent()) {
                Logger::error('Parent reporting in');
            } else {
                Logger::error('Child reporting in');
            }
            SignalHandler::$blockExit = false;
        } while ($count++ < 5);
    }


    protected function preFork()
    {
        Logger::alert('preFork() method has been called');
    }


    protected function postFork()
    {
        Logger::alert('postFork() method has been called');
    }

}