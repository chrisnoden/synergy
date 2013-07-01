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

/**
 * Class ArgumentParser
 *
 * @category Synergy\Project\Cli
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class ArgumentParser 
{

    /**
     * Parses out all the command line arguments to find the arguments
     * for the Symfony CliProject and the requested controller plus any
     * arguments to pass to the controller
     *
     * @param string $arguments line of args to be parsed (if null then taken from command line)
     *
     * @return array associative array of the request and the raw arguments for it
     */
    public function parseArguments($arguments = null)
    {
        /**
         * Store our parsed results here
         */
        $result = array();

        if (is_null($arguments)) {
            $arguments = join(' ', $_SERVER['argv']);
        }

        $request = null;
        $requestArgs = array();
        $systemArgs = array();
        $phase = 1;
        $script_filename = strtolower($_SERVER['SCRIPT_FILENAME']);

        $aArgs = explode(' ', $arguments);

        foreach ($aArgs AS $val)
        {
            switch ($phase)
            {
                case 1:
                    // look for our app/console script first
                    if (strtolower($val) == $script_filename) {
                        $phase = 2;
                        continue;
                    }
                    break;

                case 2:
                    // look for any args for Symfony
                    if (substr($val, 0, 1) == '-') {
                        $systemArgs[] = $val;
                    } else {
                        $phase = 3;
                        $request = $val;
                    }
                    break;

                case 3:
                    // look for any args for the request
                    $requestArgs[] = $val;

            }
        }

//        $this->parseSystemArgs($systemArgs);

        $result = array(
            'request' => $request,
            'arguments' => join(' ', $requestArgs)
        );

        return $result;
    }

}