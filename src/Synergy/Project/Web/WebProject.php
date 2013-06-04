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
use Synergy\Controller\Controller;
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
     * @var string name of the successful controller class
     */
    private $_controllerClassName;
    /**
     * @var string name of the successful action method
     */
    private $_controllerActionName;
    /**
     * @var array parameters to pass to the controller
     */
    private $_controllerParameters = array();
    /**
     * @var WebRequest
     */
    private $_originalWebRequest;


    /**
     * Instantiate a new Web_Handler object
     *
     * @param WebRequest $request optional WebRequest object
     */
    public function __construct(WebRequest $request = null)
    {
        // turn off automatic session starting (if enabled)
        ini_set('session.auto_start', '0');
        // @todo check this actually stops the session before it's been created

        if (is_null($request)) {
            $request = WebRequest::createFromGlobals();
        }
        // @todo remove the below hack
        if ($request->getPathInfo() == '/favicon.ico') exit;
        // Store the request as the original WebRequest
        $this->_originalWebRequest = $request;

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
        $router = new Router();
        if (defined('SYNERGY_WEB_ROOT')) {
            $filename = dirname(SYNERGY_WEB_ROOT) . '/app/config/routes.yml';
            $router->setRouteCollectionFromFile($filename);
        }

        /**
         * Get the Controller object
         */
        $controller = $router->getControllerFromRequest(
            $this->_originalWebRequest
        );
        // Store the name of the successful controller
        $this->_controllerClassName = $router->getControllerName();
        // Store the name of the successful action method
        $this->_controllerActionName = $router->getMethodName();
        // Store the parameters to be passed to the action
        $this->_controllerParameters = $router->getControllerParameters();
        // Call the action
        $response = $this->callControllerAction(
            $controller,
            $this->_controllerActionName,
            $this->_controllerParameters
        );

        // Deal with any response object that was returned
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        }
    }


    /**
     * Call the successful method in the controller class passing in the
     * necessary parameters
     *
     * @param Controller $controller the Controller object
     * @param string     $action     the method name
     * @param array      $parameters any parameters to pass
     *
     * @return mixed
     */
    protected function callControllerAction(Controller $controller, $action, $parameters = array())
    {
        if (count($parameters) > 0) {
            $aParamsToPass = array();
            $r = new \ReflectionMethod($controller->__toString(), $action);
            $classParams = $r->getParameters();
            foreach ($classParams as $argKey=>$oName) {
                $argName = (string)$oName->getName();
                if (isset($parameters[(string)$argName])) {
                    $aParamsToPass[$argKey] = $parameters[(string)$argName];
                }
            }

            $response = call_user_func_array(array($controller->__toString(), $action), $aParamsToPass);
        } else {
            $response = $controller->$action();
        }

        return $response;
    }


    /**
     * deliver the response to the browser
     *
     * @param WebResponse $response the response object
     *
     * @return void
     */
    protected function handleWebResponse(WebResponse $response)
    {
        $response
            ->prepare($this->_originalWebRequest)
            ->send();
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


    /**
     * the original WebRequest object - unmodified
     *
     * @return WebRequest the original WebRequest
     */
    public function getWebRequest()
    {
        return $this->_originalWebRequest;
    }

}
