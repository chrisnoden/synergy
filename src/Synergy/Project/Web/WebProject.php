<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * PHP version 5
 *
 * @category  Project:Synergy
 * @package   Synergy
 * @author    Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 * @link      http://chrisnoden.com
 * @license   http://opensource.org/licenses/LGPL-3.0
 */

namespace Synergy\Project\Web;

use Psr\Log\LogLevel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Synergy\Exception\InvalidControllerException;
use Synergy\Logger\Logger;
use Synergy\Project;
use Symfony\Component\HttpFoundation\Request;
use Synergy\Project\ProjectAbstract;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * Handle web template calls
 */
final class WebProject extends ProjectAbstract
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $_oRequest;
    /**
     * @var array of controller objects we need to call
     */
    private $_aRouteData = array();
    /**
     * @var \Synergy\Controller\ControllerAbstract
     */
    private $_oController;


    /**
     * Instantiate a new Web_Handler object
     */
    public function __construct()
    {
        // turn off automatic session starting (if enabled)
        ini_set('session.auto_start', '0');
        // @todo check this actually stops the session before it's been created

        parent::__construct();
    }


    /**
     * destructor - cleans up where necessary
     */
    public function __destruct()
    {
        parent::__destruct();
    }


    /**
     * Our main method : let's go and run our web project
     */
    public function launch()
    {
        /**
         * Load the Request
         */
        $this->loadRequest();
        /**
         * What route are we using
         */
        $this->findRoute();
        /**
         * What controller
         */
        $this->findController();
    }


    /**
     * Populate the oRequest object
     */
    private function loadRequest()
    {
        $this->_oRequest = Request::createFromGlobals();
    }


    /**
     * Choose the Controller for the web request
     */
    private function findRoute()
    {
        $locator    = new FileLocator(
            dirname(SYNERGY_WEB_ROOT) . DIRECTORY_SEPARATOR . 'app/config/'
        );
        $loader     = new YamlFileLoader($locator);
        $collection = $loader->load('routes.yml');
        $context    = new RequestContext();
        $context->fromRequest($this->_oRequest);
        $matcher = new UrlMatcher($collection, $context);
        try {
            $parameters = $matcher->match($this->_oRequest->getPathInfo());
        } catch (ResourceNotFoundException $ex) {
            // @todo Replace/refactor with something user-definable
            // Use our DefaultController
            $parameters = $this->getDefaultControllerParameters();
        }
        $this->_aRouteData = $parameters;
    }


    /**
     * Returns the details for the Synergy Default Controller
     *
     * @return array
     */
    private function getDefaultControllerParameters()
    {
        $parameters = array(
            '_controller' => 'Synergy\\Controller\\DefaultController',
            '_route'      => 'SynergyDefault'
        );

        return $parameters;
    }

    private function findController()
    {
        $testClass = $this->_aRouteData['_controller'];
        if (!$this->testAndSetController($testClass)) {
            throw new InvalidControllerException('Controller '.$testClass.' not found');
        }
    }

    private function testAndSetController($className)
    {
        if (class_exists($className)) {
            /**
             * @var $controller \Synergy\Controller\ControllerAbstract
             */
            $controller = new $className();
            $controller->defaultAction();

            return true;
        }
    }


}
