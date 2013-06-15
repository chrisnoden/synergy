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
use Synergy\Project\Web\WebRouter;
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
        $obj = new WebRouter(new WebRequest());
        $this->assertInstanceOf('Synergy\Project\Web\WebRouter', $obj);
        $this->assertInstanceOf('Synergy\Router\RouterAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
    }


    /**
     * Does a simple route/request combination get matched properly
     */
    public function testBasicGetMethodRoute()
    {
        $route  = new Route('/test1', array('controller' => 'MyController:test'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

        $request = WebRequest::create(
            '/test1',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('MyController', $controller->getClassName());
        $this->assertEquals('testAction', $controller->getMethodName());
    }


    /**
     * Does a simple route/request combination get matched properly
     */
    public function testBasicPostMethodRoute()
    {
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

        $request = WebRequest::create(
            '/test1',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Match the request to the route
        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('MyController', $controller->getClassName());
        $this->assertEquals('defaultAction', $controller->getMethodName());
    }


    /**
     * Does the default route get set if the URL doesn't match a route
     */
    public function testDefaultGetMethodRoute()
    {
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

        $request = WebRequest::create(
            '/test2',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('Synergy\Controller\DefaultController', $controller->getClassName());
        $this->assertEquals('defaultAction', $controller->getMethodName());
    }


    /**
     * Does the default route get set if the URL doesn't match a route
     */
    public function testDefaultPostMethodRoute()
    {
        $route  = new Route('/test1', array('controller' => 'MyController'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

        $request = WebRequest::create(
            '/test2',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('Synergy\Controller\DefaultController', $controller->getClassName());
        $this->assertEquals('defaultAction', $controller->getMethodName());
    }


    public function testHttpPostMethodRoute()
    {
        $route = new Route('/test1', array('controller' => 'MyController'));
        $route->setMethods(array('POST'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

        $request = WebRequest::create(
            '/test1',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('MyController', $controller->getClassName());

        // This GET request should fail
        $request = WebRequest::create(
            '/test1',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        // Test for the exception
//        $this->setExpectedException(
//            'Symfony\Component\Routing\Exception\MethodNotAllowedException', ''
//        );
        $obj->match();
    }


    /**
     * Tests our Extended RouteMatcher to see routing
     * for a phone or tablet works
     */
    public function testPhoneDeviceRoutingSuccess()
    {
        // Create a route and routecollection
        $route  = new Route('/mobiletest', array('controller' => 'MyController:test'), array(), array('device' => 'mobile'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

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

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('MyController', $controller->getClassName());
        $this->assertEquals('testAction', $controller->getMethodName());
    }


    /**
     * Try to match a phone device to a route that requires a tablet
     * Should fall to the DefaultController and defaultAction
     */
    public function testPhoneDeviceRoutingFail()
    {
        // Create a route and routecollection
        $route  = new Route('/mobiletest', array('controller' => 'MyController:test'), array(), array('device' => 'tablet'));
        $routes = new RouteCollection();
        $routes->add('route_name', $route);

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

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('Synergy\Controller\DefaultController', $controller->getClassName());
        $this->assertEquals('defaultAction', $controller->getMethodName());
    }


    /**
     * Creates two mobile specific routes for the same path and checks
     * that a mobile is indeed assigned the correct controller and method
     */
    public function testDeviceRoutingFallThru()
    {
        // Create a route and routecollection
        $route1  = new Route('/mobiletest', array('controller' => 'TabletController:tablet'), array(), array('device' => 'tablet'));
        $route2  = new Route('/mobiletest', array('controller' => 'MobileController:mobile'), array(), array('device' => 'mobile'));
        $routes = new RouteCollection();
        $routes->add('route1', $route1);
        $routes->add('route2', $route2);

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

        $obj = new WebRouter($request);
        // Pass our route collection to our WebRouter object
        $obj->setRouteCollection($routes);
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('MobileController', $controller->getClassName());
        $this->assertEquals('mobileAction', $controller->getMethodName());
    }


    /**
     * Creating a route for a specific mobile operating system
     * The router should return that controller & method
     */
//    public function testDeviceOsRouting()
//    {
//        // Create a route and routecollection
//        $route0  = new Route('/mobiletest', array('controller' => 'MobileController:android'), array(), array('device' => 'mobile', 'os' => 'android'));
//        $route1  = new Route('/mobiletest', array('controller' => 'MobileController:ios'), array(), array('device' => 'mobile', 'os' => 'iOS', 'type' => 'iPhone'));
//        $route2  = new Route('/mobiletest', array('controller' => 'MobileController:default'), array(), array('device' => 'mobile'));
//        $route3  = new Route('/mobiletest', array('controller' => 'GenericController:android'), array(), array('device' => 'tablet', 'os' => 'Android', 'type' => 'SonyTablet'));
//        $routes = new RouteCollection();
//        $routes->add('route0', $route0);
//        $routes->add('route1', $route1);
//        $routes->add('route2', $route2);
//        $routes->add('route3', $route3);
//
//        // Our test request
//        $request = WebRequest::create(
//            '/mobiletest',
//            'GET',
//            array('name' => 'Chris Noden')
//        );
//        $request->overrideGlobals();
//
//        // Build our fake iPhone test device object
//        $device = new \Mobile_Detect();
//        $device->setUserAgent('Mozilla/5.0 (iPhone; U; CPU iPhone OS 6_0 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/10A5355d Safari/7534.48.3');
//        // Pass it to our WebRequest so it thinks the request came from an iPhone
//        $request->setDevice($device);
//
//        $obj = new WebRouter($request);
//        // Match the request to the route
//        $obj->match();
//        $controller = $obj->getController();
//        $this->assertEquals('MobileController', $controller->getClassName());
//        $this->assertEquals('iosAction', $controller->getMethodName());
//
//        // Test using an Android Tablet
//        $device->setUserAgent('Mozilla/5.0 (Linux; U; Android 4.0.3; ja-jp; Sony Tablet P Build/TISU0085) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30');
//        $request->setDevice($device);
//
//        $obj = new WebRouter($request);
//        // Pass our route collection to our WebRouter object
//        $obj->setRouteCollection($routes);
//        // Match the request to the route
//        $obj->match();
//        $controller = $obj->getController();
//        $this->assertEquals('GenericController', $controller->getClassName());
//        $this->assertEquals('androidAction', $controller->getMethodName());
//    }


    /**
     * Load the RouteCollection from a test yml file
     */
    public function testYamlRouteFile()
    {
        $request = WebRequest::create(
            '/foo',
            'GET',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        $obj = new WebRouter($request);
        $obj->setRouteCollectionFromFile(SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'test_routes.yml');
        // Match the request to the route
        $obj->match();
        $controller = $obj->getController();
        $this->assertEquals('SynergyTest\TestController', $controller->getClassName());
        $this->assertEquals('route1', $obj->getRouteName());
        $this->assertEquals('fooAction', $controller->getMethodName());
    }


    /**
     * Load the RouteCollection from a test yml file and try a valid
     * route but with an invalid HTTP method
     */
    public function testYamlMethodFails()
    {
        // A POST request to a defined path should fail
        $request = WebRequest::create(
            '/foo',
            'POST',
            array('name' => 'Chris Noden')
        );
        $request->overrideGlobals();

        $obj = new WebRouter($request);
        $obj->setRouteCollectionFromFile(SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'test_routes.yml');

        // Test for the exception
        $this->setExpectedException(
            'Symfony\Component\Routing\Exception\MethodNotAllowedException', ''
        );
        $obj->match();
    }

}