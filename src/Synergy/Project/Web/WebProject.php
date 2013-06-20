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
use Synergy\Controller\ControllerEntity;
use Synergy\Exception\InvalidArgumentException;
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
     * @var ControllerEntity
     */
    private $_controller;
    /**
     * @var WebRequest
     */
    private $_request;
    /**
     * @var string location of the view templates
     */
    private $_templateDir;


    /**
     * Instantiate a new object
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
        $this->_request = $request;

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
    protected function launch()
    {

        $router = new WebRouter($this->_request);
        if (defined('SYNERGY_WEB_ROOT')) {
            $filename = dirname(SYNERGY_WEB_ROOT) . '/app/config/routes.yml';
            $router->setRouteCollectionFromFile($filename);
        }
        $router->match();

        /**
         * Get the ControllerEntity
         */
        $this->_controller = $router->getController();
        $this->_controller->setRequest($this->_request);
        // Call the action
        $response = $this->_controller->callControllerAction();

        // Deal with any response object that was returned
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        } else if ($response instanceof Template\TemplateAbstract) {
            $this->handleWebTemplate($response);
        }
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
            ->prepare($this->_request)
            ->send();
    }


    /**
     * Renders a WebTemplate
     *
     * @param Template\TemplateAbstract $template
     *
     * @return void
     */
    protected function handleWebTemplate(Template\TemplateAbstract $template)
    {
        $template->setCacheDir($this->getTempDir() . DIRECTORY_SEPARATOR . 'cache');
        if (is_null($template->getTemplateDir()) && isset($this->_templateDir)) {
            $template->setTemplateDir($this->_templateDir);
        }
        $template->setDev($this->isDev);
        $template->setParameters($this->_controller->getParameters());
        $template->init();
        $response = $template->getWebResponse();
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        }
    }


    /**
     * Name of the chosen controller class
     *
     * @return \Mobile_Detect
     */
    public function getControllerName()
    {
        return $this->_controller->getClassName();
    }


    /**
     * Value of member _controller
     *
     * @return \Synergy\Controller\ControllerEntity value of member
     */
    public function getController()
    {
        return $this->_controller;
    }


    /**
     * the original WebRequest object - unmodified
     *
     * @return WebRequest the original WebRequest
     */
    public function getWebRequest()
    {
        return $this->_request;
    }


    /**
     * directory where the view templates are located
     *
     * @param string $dir directory where the view templates are located
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setTemplateDir($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
            );
        } else if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not writable", $dir)
            );
        } else {
            $this->_request->setTemplateDir($dir);
            $this->_templateDir = $dir;
        }
    }


    /**
     * directory where the view templates are located
     *
     * @return string directory where the view templates are located
     */
    public function getTemplateDir()
    {
        return $this->_templateDir;
    }

}
