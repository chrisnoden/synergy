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

    public function __construct()
    {
        // Check this is coming from the CLI
        if (PHP_SAPI !== 'cli') {
            throw new SynergyException(
                sprintf('%s must be run from command line project', __CLASS__)
            );
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


}