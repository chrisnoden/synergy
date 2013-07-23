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
     * @var string
     */
    protected $request;
    /**
     * @var string
     */
    protected $rawArgs;



    /**
     * Parses out all the command line to find the requested controller plus
     * any arguments which are available to both Synergy framework
     * classes and to the Controller class
     *
     * @param string $arguments line of args to be parsed (if null then taken from command line)
     *
     * @return ArgumentParser
     */
    public static function parseArguments($arguments = null)
    {
        $obj = new ArgumentParser();

        if (is_null($arguments) && isset($_SERVER['argv'])) {
            $arguments = join(' ', $_SERVER['argv']);
        } else if (is_null($arguments)) {
            return $obj;
        }

        $request = null;
        $requestArgs = array();
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
                    // look for any args for Synergy
                    if (substr($val, 0, 1) == '-') {
                        $requestArgs[] = $val;
                    } else {
                        $phase = 3;
                        $obj->setRequest($val);
                    }
                    break;

                case 3:
                    // look for any args for the request
                    $requestArgs[] = $val;

            }
        }

        $obj->setRawArgs(join(' ', $requestArgs));

        return $obj;
    }


    /**
     * Look in the command line args for the argument and return the value if found
     *
     * @param $argName
     *
     * @return mixed|bool
     */
    public function arg($argName)
    {
        $elements = explode(' ', $this->rawArgs);
        for ($item=0; $item<count($elements); $item++) {
            $test = preg_replace('/^[\-]+/', '', $elements[$item]);
            if (strpos($test, '=')) {
                $arr = explode('=', $test, 2);
                if (strtolower($arr[0]) == $argName) {
                    return $arr[1];
                }
            }
        }

        return false;
    }


    /**
     * Tests for a switch (eg -v -vv -S) in the command line arguments
     *
     * @param $switch
     *
     * @return bool
     */
    public function hasSwitch($switch)
    {
        $elements = explode(' ', $this->rawArgs);
        for ($item=0; $item<count($elements); $item++) {
            $test = $elements[$item];
            if (substr($test, 0, 1) == '-' && substr($test, 1) == $switch && !strpos($test, '=')) {
                return true;
            }
        }

        return false;
    }


    /**
     * Value of member request
     *
     * @return string value of member
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Value of member rawArgs
     *
     * @return string value of member
     */
    public function getRawArgs()
    {
        return $this->rawArgs;
    }


    /**
     * Set the value of rawArgs member
     *
     * @param string $rawArgs
     *
     * @return void
     */
    public function setRawArgs($rawArgs)
    {
        $this->rawArgs = $rawArgs;
    }


    /**
     * Set the value of request member
     *
     * @param string $request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

}