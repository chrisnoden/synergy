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

namespace Synergy\Controller;

use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\InvalidControllerException;

/**
 * Class Parser
 * Finds a class and optional method to match a delimited string
 * eg: TestProject\MyController:newMethod
 * This will find a class called TestProject\MyController and the method newMethod()
 *
 * @category Synergy\Controller
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Parser 
{

    /**
     * @var string
     */
    private $_controller;
    /**
     * @var string
     */
    private $_method;


    /**
     * Parse the action string into a valid controller and method
     *
     * @param string $actionShorthand the shorthand
     *
     * @return Parser
     * @throws InvalidArgumentException
     */
    public function __construct($actionShorthand)
    {
        $this->_parseControllerString($actionShorthand);
        return $this;
    }


    /**
     * Sets the $_controller and $_method data
     *
     * @param string $controller_string controller & action colon delimited
     *
     * @return void
     */
    private function _parseControllerString($controller_string)
    {
        if (strpos($controller_string, ':')) {
            $arr               = explode(':', $controller_string);
            $this->_controller = $arr[0];
            $this->_method     = $arr[1] . 'Action';
        } else {
            $this->_controller = $controller_string;
            $this->_method     = 'defaultAction';
        }
    }


    /**
     * Check the controller with the given name exists and is accessible
     *
     * @param string $controllerName controller name
     *
     * @return bool true if class exists
     */
    private function _validController($controllerName)
    {
        if (class_exists($controllerName)) {
            return true;
        }
        return false;
    }


    /**
     * Name of the method (Action) that the route references
     *
     * @return string name of the successful method
     */
    public function getMethodName()
    {
        return $this->_method;
    }


    /**
     * Name of the controller class that the route references
     *
     * @return string Name of the successful controller
     */
    public function getControllerName()
    {
        return $this->_controller;
    }


}