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
 * @category  Test
 * @package   Synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Project\Web;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Synergy\Project\Web\Router;
use Synergy\Project\Web\WebRequest;

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
        $obj    = new Router();
        $route  = new Route('/test1', array('controller' => 'MyController:test'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = WebRequest::create(
            '/test1',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
//        $this->assertEquals('MyController', $obj->getControllerName());
//        $this->assertEquals('testAction', $obj->getMethodName());
    }


    /**
     * Does a simple route/request combination get matched properly
     */
    public function testBasicPostMethodRoute()
    {
        $obj    = new Router();
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = WebRequest::create(
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
        $obj    = new Router();
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = WebRequest::create(
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
        $obj    = new Router();
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = WebRequest::create(
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
        $obj   = new Router();
        $route = new Route('/test1', array('controller' => 'MyController'));
        $route->setMethods(array('POST'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        $request = WebRequest::create(
            '/test1',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MyController', $obj->getControllerName());

        // This GET request should fail
        $request = WebRequest::create(
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
     * Tests our Extended RouteMatcher to see routing
     * for a phone or tablet works
     */
    public function testPhoneDeviceRoutingSuccess()
    {
        $obj = new Router();
        // Create a route and routecollection
        $route  = new Route('/mobiletest', array('controller' => 'MyController:test'), array(), array('device' => 'mobile'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        // Our test request
        $request = WebRequest::create(
            '/mobiletest',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Build our fake iPhone test device object
        $device = new \Mobile_Detect();
        $device->setUserAgent('Mozilla/5.0 (iPhone; U; CPU iPhone OS 6_0 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/10A5355d Safari/7534.48.3');
        // Pass it to our WebRequest so it thinks the request came from an iPhone
        $request->setDevice($device);

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MyController', $obj->getControllerName());
        $this->assertEquals('testAction', $obj->getMethodName());
    }


    /**
     * Try to match a phone device to a route that requires a tablet
     * Should fall to the DefaultController and defaultAction
     */
    public function testPhoneDeviceRoutingFail()
    {
        $obj = new Router();
        // Create a route and routecollection
        $route  = new Route('/mobiletest', array('controller' => 'MyController:test'), array(), array('device' => 'tablet'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        // Our test request
        $request = WebRequest::create(
            '/mobiletest',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Build our fake iPhone test device object
        $device = new \Mobile_Detect();
        $device->setUserAgent('Mozilla/5.0 (iPhone; U; CPU iPhone OS 6_0 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/10A5355d Safari/7534.48.3');
        // Pass it to our WebRequest so it thinks the request came from an iPhone
        $request->setDevice($device);

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('Synergy\Controller\DefaultController', $obj->getControllerName());
        $this->assertEquals('defaultAction', $obj->getMethodName());
    }


    /**
     * Creates two mobile specific routes for the same path and checks
     * that a mobile is indeed assigned the correct controller and method
     */
    public function testDeviceRoutingFallThru()
    {
        $obj = new Router();
        // Create a route and routecollection
        $route1  = new Route('/mobiletest', array('controller' => 'TabletController:tablet'), array(), array('device' => 'tablet'));
        $route2  = new Route('/mobiletest', array('controller' => 'MobileController:mobile'), array(), array('device' => 'mobile'));
        $routes = new RouteCollection();
        $routes->add('route1', $route1);
        $routes->add('route2', $route2);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        // Our test request
        $request = WebRequest::create(
            '/mobiletest',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Build our fake iPhone test device object
        $device = new \Mobile_Detect();
        $device->setUserAgent('Mozilla/5.0 (iPhone; U; CPU iPhone OS 6_0 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/10A5355d Safari/7534.48.3');
        // Pass it to our WebRequest so it thinks the request came from an iPhone
        $request->setDevice($device);

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MobileController', $obj->getControllerName());
        $this->assertEquals('mobileAction', $obj->getMethodName());
    }


    /**
     * Creating a route for a specific mobile operating system
     * The router should return that controller & method
     */
    public function testDeviceOsRouting()
    {
        $obj = new Router();
        // Create a route and routecollection
        $route0  = new Route('/mobiletest', array('controller' => 'MobileController:android'), array(), array('device' => 'mobile', 'os' => 'android'));
        $route1  = new Route('/mobiletest', array('controller' => 'MobileController:ios'), array(), array('device' => 'mobile', 'os' => 'iOS'));
        $route2  = new Route('/mobiletest', array('controller' => 'MobileController:default'), array(), array('device' => 'mobile'));
        $route3  = new Route('/mobiletest', array('controller' => 'GenericController:android'), array(), array('os' => 'Android'));
        $routes = new RouteCollection();
        $routes->add('route0', $route1);
        $routes->add('route1', $route1);
        $routes->add('route2', $route2);
        $routes->add('route3', $route3);
        // Pass our route collection to our Router object
        $obj->setRouteCollection($routes);

        // Our test request
        $request = WebRequest::create(
            '/mobiletest',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Build our fake iPhone test device object
        $device = new \Mobile_Detect();
        $device->setUserAgent('Mozilla/5.0 (iPhone; U; CPU iPhone OS 6_0 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/10A5355d Safari/7534.48.3');
        // Pass it to our WebRequest so it thinks the request came from an iPhone
        $request->setDevice($device);

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('MobileController', $obj->getControllerName());
        $this->assertEquals('iosAction', $obj->getMethodName());

        // Test using an Android Tablet
        $device->setUserAgent('Mozilla/5.0 (Linux; U; Android 4.0.3; ja-jp; Sony Tablet P Build/TISU0085) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30');
        $request->setDevice($device);

        // Match the request to the route
        $obj->match($request);
        $this->assertEquals('GenericController', $obj->getControllerName());
        $this->assertEquals('androidAction', $obj->getMethodName());

    }


    /**
     * Load the RouteCollection from a test yml file
     */
    public function testYamlRouteFile()
    {
        $obj = new Router();
        $obj->setRouteCollectionFromFile(SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'test_routes.yml');

        $request = WebRequest::create(
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
        $request = WebRequest::create(
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