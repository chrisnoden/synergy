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

use Symfony\Component\HttpFoundation\Request;
use Synergy\Logger\Logger;
use Synergy\Project;
use Synergy\Project\ProjectAbstract;

/**
 * Class WebProject
 * Handles web projects
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
final class WebProject extends ProjectAbstract
{

    /**
     * @var string name of the chosen controller class
     */
    private $_controllerClassName;

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
     *
     * @return void
     */
    public function launch()
    {
        $Router = new Router();
        $controller = $Router->getControllerFromGlobals();

        $this->_controllerClassName = $controller->__toString();
        $method = $Router->getMethodName();
        $controller->$method();
    }


    /**
     * Name of the chosen controller class
     *
     * @return \Mobile_Detect
     */
    public function getControllerName()
    {
        return $this->_controllerClassName;
    }

}
