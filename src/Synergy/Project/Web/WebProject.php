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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Synergy\Exception\InvalidControllerException;
use Synergy\Logger\Logger;
use Synergy\Project;
use Synergy\Project\ProjectAbstract;

/**
 * Handle web template calls
 */
final class WebProject extends ProjectAbstract
{



    /**
     * Instantiate a new Web_Handler object
     */
    public function __construct()
    {
        // turn off automatic session starting (if enabled)
        ini_set('session.auto_start', '0');
        // @todo check this actually stops the session before it's been created

        // @todo remove the below hack
        $request = Request::createFromGlobals();
        if ($request->getPathInfo() == '/favicon.ico') exit;

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
        $Router = new Router();
        $controller = $Router->getControllerFromGlobals();
        $method = $Router->getMethodName();
        $controller->$method();
    }




}
