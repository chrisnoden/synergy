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

use Symfony\Component\HttpFoundation\Request;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\ProjectException;
use Synergy\Logger\Logger;
use Synergy\Object;
use Synergy\Exception\InvalidControllerException;
use Synergy\Project\ProjectAbstract;
use Synergy\Project\Web\WebAsset;

/**
 * Class ControllerEntity
 * A holder for the Controller class and method that will be run
 * by the Project
 *
 * @category Synergy\Controller
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class ControllerEntity extends Object
{

    /**
     * @var string
     */
    protected $className;
    /**
     * @var string
     */
    protected $methodName;
    /**
     * @var array params passed to the controller
     */
    protected $parameters = array();
    /**
     * @var array params the controller wishes to share
     */
    protected $controllerParameters = array();
    /**
     * @var ControllerEntity
     */
    protected $controller;
    /**
     * @var string suffic to add to the method name from the controller
     */
    protected $methodSuffix = 'Action';
    /**
     * @var string default method called if not specified
     */
    protected $defaultMethodName = 'default';
    /**
     * @var string default class called if route not matched
     */
    protected $defaultClassName = 'Synergy\Controller\DefaultController';
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    /**
     * @var \Synergy\Project\ProjectAbstract
     */
    protected $project;


    /**
     * Set the class name
     *
     * @param string $className name of the class
     *
     * @return void
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }


    /**
     * Name of the class to instantiate
     *
     * @return string name of the class
     */
    public function getClassName()
    {
        return $this->className;
    }


    /**
     * Name of the method to call
     *
     * @param string $methodName name of the method to call
     *
     * @return void
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }


    /**
     * Name of the method to call
     *
     * @return string name of the method to call
     */
    public function getMethodName()
    {
        return $this->methodName;
    }


    /**
     * Controller string notation from the route
     *
     * @param string $controller the route controller notation
     *
     * @return void
     */
    public function setController($controller)
    {
        $this->parseController($controller);
        $this->controller = $controller;
    }


    /**
     * Controller string notation from the route
     *
     * @return string the route controller notation
     */
    public function getController()
    {
        return $this->controller;
    }


    /**
     * The original parameters set for the Controller
     *
     * @param array $parameters passed to the method
     *
     * @return void
     */
    public function setParameters(Array $parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * The original parameters for the Controller
     *
     * @return array associative array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * Parameters the controller wishes to share
     *
     * @return array
     */
    public function getControllerParameters()
    {
        return $this->controllerParameters;
    }


    /**
     * Set the value of controllerParameters member
     *
     * @param array $controllerParameters
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setControllerParameters($controllerParameters)
    {
        if (is_array($controllerParameters)) {
            $this->controllerParameters = $controllerParameters;
        } else {
            throw new InvalidArgumentException(
                __METHOD__ . ' expects array as argument'
            );
        }
    }


    /**
     * @param string $defaultClassName
     */
    public function setDefaultClassName($defaultClassName)
    {
        $this->defaultClassName = $defaultClassName;
    }


    /**
     * @return string
     */
    public function getDefaultClassName()
    {
        return $this->defaultClassName;
    }


    /**
     * @param string $defaultMethodName
     */
    public function setDefaultMethodName($defaultMethodName)
    {
        $this->defaultMethodName = $defaultMethodName;
    }


    /**
     * @return string
     */
    public function getDefaultMethodName()
    {
        return $this->defaultMethodName;
    }


    /**
     * @param string $methodSuffix
     */
    public function setMethodSuffix($methodSuffix)
    {
        $this->methodSuffix = $methodSuffix;
    }


    /**
     * @return string
     */
    public function getMethodSuffix()
    {
        return $this->methodSuffix;
    }


    /**
     * Set the request object which is used by some controllers
     *
     * @param \Symfony\Component\HttpFoundation\Request $request request object is used by some controllers
     *
     * @return void
     */
    public function setRequest($request)
    {
        if ($request instanceof Request) {
            $this->request = $request;
        } else {
            throw new InvalidArgumentException(
                '$request must be an instance of \Symfony\Component\HttpFoundation\Request'
            );
        }
    }


    /**
     * request object is used by some controllers
     *
     * @return \Symfony\Component\HttpFoundation\Request value of member
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Set the value of project member
     *
     * @param ProjectAbstract $project
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setProject($project)
    {
        if ($project instanceof ProjectAbstract) {
            $this->project = $project;
        } else {
            throw new InvalidArgumentException(
                '$project must be an instance of \\Synergy\\Project\\ProjectAbstract'
            );
        }
    }


    /**
     * Value of member project
     *
     * @return \Synergy\Project\ProjectAbstract value of member
     */
    public function getProject()
    {
        return $this->project;
    }


    /**
     * Parse the controller into the class and method names
     *
     * @param string $controller controller & action colon delimited
     *
     * @return void
     */
    protected function parseController($controller)
    {
        if (strpos($controller, ':')) {
            $arr              = explode(':', $controller);
            $this->className  = $arr[0];
            $this->methodName = $arr[1] . $this->methodSuffix;
        } else {
            $this->className  = $controller;
            $this->methodName = $this->defaultMethodName . $this->methodSuffix;
        }
    }


    /**
     * Tests the class exists
     *
     * @param string $className name of the class to test
     *
     * @throws \Synergy\Exception\InvalidControllerException
     * @throws \Synergy\Exception\InvalidArgumentException
     * @return void
     */
    protected function testClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Expecting string for className'
            );
        } else if (strlen($className) == 0) {
            throw new InvalidArgumentException(
                'className not set'
            );
        }
        if (!class_exists($className)) {
            throw new InvalidControllerException(
                sprintf(
                    "%s class not found",
                    $className
                )
            );
        }
    }


    /**
     * Tests the class::method() is callable
     *
     * @param string $className  name of the class
     * @param string $methodName name of the method to test
     *
     * @throws \Synergy\Exception\InvalidControllerException
     * @throws \Synergy\Exception\InvalidArgumentException
     * @return void
     */
    protected function testAction($className, $methodName)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Expecting string for className'
            );
        } else if (strlen($className) == 0) {
            throw new InvalidArgumentException(
                'className not set'
            );
        } else if (!is_string($methodName)) {
            throw new InvalidArgumentException(
                'Expecting string for methodName'
            );
        } else if (strlen($methodName) == 0) {
            throw new InvalidArgumentException(
                'methodName not set'
            );
        }
        if (!is_callable(array($className, $methodName))) {
            throw new InvalidControllerException(
                sprintf(
                    "%s::%s() is not callable",
                    $className,
                    $methodName
                )
            );
        }
    }


    /**
     * Extracts just the valid parameters to pass to the method
     *
     * @param string $className  name of the class
     * @param string $methodName name of the method
     * @param array  $parameters associative array of available params
     *
     * @return array associative array of parameters to pass to the method
     * @throws \Synergy\Exception\ProjectException
     */
    protected function buildActionParameters($className, $methodName, Array $parameters)
    {
        // Initialise the array of parameters for the action method
        $aParamsToPass = array();

        // How many parameters does the controller action method expect
        $r = new \ReflectionMethod($className, $methodName);
        $classParams = $r->getParameters();
        // Populate the parameters for the controller
        foreach ($classParams as $argKey=>$oName) {
            $argName = (string)$oName->getName();
            if (isset($parameters[(string)$argName])) {
                $aParamsToPass[$argKey] = $parameters[(string)$argName];
            }
        }

        // If we don't have enough params for the action method then throw
        // an exception
        if (count($aParamsToPass) != count($classParams)) {
            throw new ProjectException(
                sprintf(
                    "%s::%s() expects %s parameter%s",
                    $className,
                    $methodName,
                    count($classParams),
                    count($classParams) == 1 ? '' : 's'
                )
            );
        }

        return $aParamsToPass;
    }


    /**
     * Call the successful method in the controller class passing in any
     * additional (optional) parameters
     * If you pass an array of optional parameters then it will replace
     * any pre-established parameters
     *
     * @param array $parameters any parameters to pass
     *
     * @return mixed
     * @throws InvalidControllerException
     * @throws ProjectException
     */
    public function callControllerAction($parameters = array())
    {
        $parameters = array_merge($this->parameters, $parameters);

        $this->testClass($this->className);
        $this->testAction($this->className, $this->methodName);

        $aParamsToPass = $this->buildActionParameters(
            $this->className,
            $this->methodName,
            $parameters
        );

        $response = $this->launchAction(
            $this->className,
            $this->methodName,
            $aParamsToPass
        );

        return $response;
    }


    /**
     * Instantiate and return a new object from the className
     *
     * @param string $className name of the class to instantiate
     *
     * @return Controller
     */
    protected function instantiateObject($className)
    {
        /**
         * @var Controller $object
         */
        $object = new $className();
        if (isset($this->project)) {
            $object->setProject($this->project);
        }

        return $object;
    }


    /**
     * Launch (call) the class::method() passing any param
     *
     * @param string $className  name of the class
     * @param string $methodName name of the method
     * @param array  $parameters associative array of params to pass
     *
     * @return mixed the response from the action
     * @throws InvalidArgumentException
     */
    protected function launchAction($className, $methodName, Array $parameters)
    {
        $object = $this->instantiateObject($className);

        if (!is_null($this->request)) {
            $object->setRequest($this->request);
        }

        // quick delivery of WebAsset files
        if ($object instanceof SmartController) {
            $response = $object->requestMatch($object->getRequest());
            if ($response instanceof WebAsset) {
                return $response;
            }
        }

        // This is quicker than call_user_func_array
        switch(count($parameters)) {
            case 0:
                $response = $object->{$methodName}();
                break;
            case 1:
                $response = $object->{$methodName}($parameters[0]);
                break;
            case 2:
                $response = $object->{$methodName}($parameters[0], $parameters[1]);
                break;
            case 3:
                $response = $object->{$methodName}($parameters[0], $parameters[1], $parameters[2]);
                break;
            case 4:
                $response = $object->{$methodName}($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
                break;
            case 5:
                $response = $object->{$methodName}($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);
                break;
            default:
                $response = call_user_func_array(array($className, $methodName), $parameters);
        }

        if (method_exists($object, 'getParameters')) {
            try {
                $this->setControllerParameters($object->getParameters());
            }
            catch (InvalidArgumentException $ex) {
                throw new InvalidArgumentException(sprintf(
                    'getParameters() method in %s must return array',
                    $this->className
                ));
            }
        }

        return $response;
    }

}