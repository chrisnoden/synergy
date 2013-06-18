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

namespace Synergy\Router;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Synergy\Controller\ControllerEntity;
use Synergy\Object;

/**
 * Class RouterAbstract
 *
 * @category Synergy\WebRouter
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
abstract class RouterAbstract extends Object
{

    /**
     * @var RouteCollection
     */
    protected $routeCollection;
    /**
     * @var string name of the matching route
     */
    protected $route;
    /**
     * @var ControllerEntity
     */
    protected $controller;
    /**
     * @var mixed the request to match to the route
     */
    protected $request;
    /**
     * @var string the fall-thru class if no route matched
     */
    protected $defaultClass = 'Synergy\\Controller\\DefaultController';
    /**
     * @var string name of the fall-thru class route
     */
    protected $defaultRoute = 'SynergyDefault';
    /**
     * @var array parameters to pass to the controller
     */
    protected $parameters = array();




    /**
     * Set up the Router
     *
     * @param mixed $request the request to match to the route
     */
    public function __construct($request)
    {
        $this->request = $request;
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
        $this->routeCollection = $collection;
    }


    /**
     * The RouteCollection that is being used to match against
     *
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
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

        $this->routeCollection = $collection;
    }


    /**
     * Returns the details for the Synergy Default Controller
     *
     * @return array
     */
    protected function getDefaultController()
    {
        $parameters = array(
            '_controller' => $this->defaultClass,
            '_route'      => $this->defaultRoute
        );

        return $parameters;
    }


    /**
     * Parses the array returned from the UrlMatcher call
     * and stores in our object data
     *
     * @param array $aData parameter array returned from the UrlMatcher
     *
     * @return void
     */
    protected function parseRouteParameters($aData)
    {
        foreach ($aData AS $key => $val) {
            switch (strtolower($key))
            {
                case '_controller':
                case 'controller':
                    $this->controller = $this->fetchControllerEntity($val);
                    break;

                case '_route':
                case 'route':
                    $this->route = $val;
                    break;

                default:
                    $this->parameters[$key] = $val;
            }
        }
    }


    /**
     * Return the matching ControllerEntity for the route controller
     *
     * @param string $controller the controller string from the route
     *
     * @return ControllerEntity
     */
    protected function fetchControllerEntity($controller)
    {
        $entity = new ControllerEntity();
        $entity->setController($controller);

        return $entity;
    }


    /**
     * Runs the search against our RouteCollection and returns
     * a ControllerEntity object with the matched Controller, Method and
     * Parameters
     *
     * @return void
     */
    public function match()
    {
        $this->matchRoute();
    }


    /**
     * @return mixed
     */
    abstract protected function matchRoute();


    /**
     * controller defined in the successful route
     *
     * @return ControllerEntity the controller defined by the route
     */
    public function getController()
    {
        $this->controller->setParameters($this->parameters);
        return $this->controller;
    }


    /**
     * Name of the matched route
     *
     * @return string Name of the matched route
     */
    public function getRouteName()
    {
        return $this->route;
    }

}