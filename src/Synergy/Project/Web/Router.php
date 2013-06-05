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

namespace Synergy\Project\Web;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Synergy\Exception\InvalidControllerException;
use Synergy\Logger\Logger;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Synergy\Project\RouterAbstract;

/**
 * Class Router
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Router extends RouterAbstract
{

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    private $_routeCollection;
    /**
     * @var string name of the matching route
     */
    private $_route;
    /**
     * @var string name of the winning controller
     */
    private $_controller;
    /**
     * @var string name of the winning method/action
     */
    private $_method;
    /**
     * @var array parameters to pass to the method
     */
    private $_parameters;
    /**
     * @var \Mobile_Detect mobile device type
     */
    private $_device;


    /**
     * Attempt to match the request against the routecollection
     *
     * @param WebRequest $request a WebRequest object
     *
     * @return void
     */
    private function _run(WebRequest $request)
    {
        $context = new WebRequestContext();
        $context->fromWebRequest($request);

        if (isset($this->_routeCollection)) {
            $matcher = new RouteMatcher($this->_routeCollection, $context);
            try {
                $parameters = $matcher->match($request->getPathInfo());
            } catch (ResourceNotFoundException $ex) {
                // @todo Replace/refactor with something user-definable
                // Use our DefaultController
            }
        }

        if (!isset($parameters)) {
            $parameters = $this->_getDefaultControllerParameters();
        }

        $this->_storeDataFromRouteParameters($parameters);
        Logger::info("Route name: " . $this->_route);
        Logger::info("Controller ClassName: " . $this->_controller);
        Logger::info("Controller Action: " . $this->_method);
    }


    /**
     * Set the RouteCollection used to match the route
     *
     * @param RouteCollection $collection our collection of Route objects
     *
     * @return void
     */
    public function setRouteCollection(RouteCollection $collection)
    {
        $this->_routeCollection = $collection;
    }


    /**
     * The RouteCollection that is being used to match against
     *
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->_routeCollection;
    }


    /**
     * Set the RouteCollection by parsing a routes file
     *
     * @param string $filename absolute filename of the route config
     *
     * @return void
     */
    public function setRouteCollectionFromFile($filename)
    {
        $collection = new RouteCollection();

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $dirname   = pathinfo($filename, PATHINFO_DIRNAME);
        $basename  = pathinfo($filename, PATHINFO_BASENAME);
        switch (strtolower($extension)) {
            case 'yml':
                $locator    = new FileLocator($dirname);
                $loader     = new YamlFileLoader($locator);
                $collection = $loader->load($basename);
                break;
        }

        $this->_routeCollection = $collection;
    }


    /**
     * Parses the array returned from the UrlMatcher call
     * and stores in our object data
     *
     * @param array $aData parameter array returned from the UrlMatcher
     *
     * @return void
     */
    private function _storeDataFromRouteParameters($aData)
    {
        foreach ($aData AS $key => $val) {
            switch (strtolower($key))
            {
                case '_controller':
                case 'controller':
                    $this->_parseControllerString($val);
                    break;

                case '_route':
                case 'route':
                    $this->_route = $val;
                    break;

                default:
                    $this->_parameters[$key] = $val;
            }
        }
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
     * Returns the details for the Synergy Default Controller
     *
     * @return array
     */
    private function _getDefaultControllerParameters()
    {
        $parameters = array(
            '_controller' => 'Synergy\\Controller\\DefaultController',
            '_route'      => 'SynergyDefault'
        );

        return $parameters;
    }


    /**
     * Check the controller with the given name exists and is accessible
     *
     * @param string $controllerName string name controller
     *
     * @return bool
     * @throws \Synergy\Exception\InvalidControllerException
     */
    public function validController($controllerName = null)
    {
        if (is_null($controllerName) && !is_null($this->_controller)) {
            $controllerName = $this->_controller;
        }

        if (is_null($controllerName)) {
            return false;
        }

        if (class_exists($controllerName)) {
            return true;
        }
        throw new InvalidControllerException(
            'Controller ' . $controllerName . ' not found'
        );
    }


    /**
     * Derive a successful controller and method from a Request object
     *
     * @param WebRequest $request request object
     *
     * @return mixed
     * @throws \Synergy\Exception\InvalidControllerException
     */
    public function getControllerFromRequest(WebRequest $request)
    {
        $this->_run($request);
        if ($this->validController()) {
            $controller = new $this->_controller();
            return $controller;
        }

        throw new InvalidControllerException("Unable to locate a valid controller");
    }


    /**
     * Match the request to a route and populate our object data
     * Doesn't verify the Controller is valid
     *
     * @param WebRequest $request request object
     *
     * @return void
     */
    public function match(WebRequest $request)
    {
        $this->_run($request);
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


    /**
     * Name of the matched route
     *
     * @return string Name of the matched route
     */
    public function getRouteName()
    {
        return $this->_route;
    }


    /**
     * Mobile Device info from the mobiledetect\Mobile_Detect library
     *
     * @param \Mobile_Detect $device device object
     *
     * @return void
     */
    public function setDevice(\Mobile_Detect $device)
    {
        $this->_device = $device;
    }


    /**
     * Device object
     *
     * @return \Mobile_Detect Device object
     */
    public function getDevice()
    {
        return $this->_device;
    }


    /**
     * Any parameters to be passed to the Controller Action Method
     *
     * @return array any parameters to be passed to the Controller Action
     */
    public function getControllerParameters()
    {
        return $this->_parameters;
    }

}