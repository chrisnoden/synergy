<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 * 
 * PHP version 5
 *
 * @category  Synergy:Synergy\Tests\Project\Web
 * @package   Synergy
 * @author    Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 * @link      http://chrisnoden.com
 * @license   http://opensource.org/licenses/LGPL-3.0
 */

namespace Synergy\Tests\Project\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Synergy\Project\Web\Router;

/**
 * Class RouterTest
 *
 * @package Synergy\Tests\Project\Web
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Basic object tests
     */
    public function testObject()
    {
        $obj = new Router();
        $this->assertInstanceOf('Synergy\Project\Web\Router', $obj);
        $this->assertInstanceOf('Synergy\Project\RouterAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
    }


    /**
     * Does a simple route/request combination get matched properly
     */
    public function testBasicGetMethodRoute()
    {
        $obj = new Router();
        $route = new Route('/test1', array('controller' => 'MyController:test'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = Request::create(
            '/test1',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MyController', $obj->getControllerName());
        $this->assertEquals('testAction', $obj->getMethodName());
    }


    /**
     * Does a simple route/request combination get matched properly
     */
    public function testBasicPostMethodRoute()
    {
        $obj = new Router();
        $route = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = Request::create(
            '/test1',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MyController', $obj->getControllerName());
    }


    /**
     * Does the default route get set if the URL doesn't match a route
     */
    public function testDefaultGetMethodRoute()
    {
        $obj = new Router();
        $route = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = Request::create(
            '/test2',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('Synergy\Controller\DefaultController', $obj->getControllerName());
    }


    /**
     * Does the default route get set if the URL doesn't match a route
     */
    public function testDefaultPostMethodRoute()
    {
        $obj = new Router();
        $route = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = Request::create(
            '/test2',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('Synergy\Controller\DefaultController', $obj->getControllerName());
    }


    public function testHttpPostMethodRoute()
    {
        $obj = new Router();
        $route = new Route('/test1', array('controller' => 'MyController'));
        $route->setMethods(array('POST'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = Request::create(
            '/test1',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MyController', $obj->getControllerName());

        // This GET request should fail
        $request = Request::create(
            '/test1',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Test for the exception
        $this->setExpectedException(
            'Symfony\Component\Routing\Exception\MethodNotAllowedException', ''
        );
        $obj->match($request);
    }


    /**
     * Load the RouteCollection from a test yml file
     */
    public function testYamlRouteFile()
    {
        $obj = new Router();
        $obj->setRouteCollectionFromFile(SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'test_routes.yml');

        $request = Request::create(
            '/foo',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('SynergyTest\TestController', $obj->getControllerName());
        $this->assertEquals('route1', $obj->getRouteName());
        $this->assertEquals('fooAction', $obj->getMethodName());
    }


    /**
     * Load the RouteCollection from a test yml file and try a valid
     * route but with an invalid HTTP method
     */
    public function testYamlMethodFails()
    {
        $obj = new Router();
        $obj->setRouteCollectionFromFile(SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'test_routes.yml');

        // A POST request to a defined path should fail
        $request = Request::create(
            '/foo',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Test for the exception
        $this->setExpectedException(
            'Symfony\Component\Routing\Exception\MethodNotAllowedException', ''
        );
        $obj->match($request);
    }

}