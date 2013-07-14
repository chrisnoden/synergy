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
use Synergy\Exception\NotFoundException;
use Synergy\Exception\ProjectException;
use Synergy\Exception\SynergyException;
use Synergy\Logger\Logger;
use Synergy\Project;
use Synergy\Project\ProjectAbstract;
use Synergy\View\HtmlTemplate;
use Synergy\View\TemplateAbstract;

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
        // Store the request
        $this->_request = $request;

        parent::__construct();

        Logger::debug(
            sprintf('Web Request: %s', $request->getPathInfo())
        );
    }


    /**
     * destructor - cleans up where necessary
     */
    public function __destruct()
    {
        parent::__destruct();
    }


    /**
     * Checks everything is good with our project before we run it
     *
     * @throws ProjectException
     */
    protected function checkEnv()
    {
        parent::checkEnv();

        if (!isset($this->_templateDir) && $this->getOption('synergy:webproject:template_dir')) {
            try {
                $this->setTemplateDir($this->getOption('synergy:webproject:template_dir'));
                Logger::debug('Template Dir: '.$this->_templateDir);
            }
            catch (InvalidArgumentException $ex) {
                throw new ProjectException(
                    'Unable to find or use your template directory: '.$this->getOption('synergy:webproject:template_dir')
                );
            }
        } else if (!$this->searchTemplateDir()) {
            throw new ProjectException(
                'Need to set a template_dir in the config'
            );
        }
    }


    /**
     * Our main method : let's go and run our web project
     *
     * @return void
     */
    protected function launch()
    {
        $router = new WebRouter($this->_request);

        // use the best routes config
        if ($this->getOption('synergy:routes') && file_exists($this->getOption('synergy:routes'))) {
            $filename = $this->getOption('synergy:routes');
        } else if (isset($this->configFilename) && file_exists(dirname($this->configFilename) . DIRECTORY_SEPARATOR . 'routes.yml')) {
            $filename = dirname($this->configFilename) . DIRECTORY_SEPARATOR . 'routes.yml';
        } else if (isset($this->app_dir) && file_exists($this->app_dir . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'routes.yml')) {
            $filename = $this->app_dir . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'routes.yml';
        } else if (isset($this->app_dir) && file_exists($this->app_dir . DIRECTORY_SEPARATOR . 'Config'. DIRECTORY_SEPARATOR . 'routes.yml')) {
            $filename = $this->app_dir . DIRECTORY_SEPARATOR . 'Config'. DIRECTORY_SEPARATOR . 'routes.yml';
        }

        if (isset($filename)) {
            Logger::debug(
                'RouteCollection from file: '.$filename
            );
            $router->setRouteCollectionFromFile($filename);
        }
        $router->match();

        /**
         * Get the ControllerEntity
         */
        $this->controller = $router->getController();
        $this->controller->setProject($this);
        $this->controller->setRequest($this->_request);
        // Call the action
        $response = $this->controller->callControllerAction();

        // Deal with any response object that was returned
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        } else if ($response instanceof TemplateAbstract) {
            $this->handleWebTemplate($response);
        } else if ($response instanceof WebAsset) {
            $response->deliver();
        } else if (is_string($response)) {
            $render = WebResponse::create($response);
            $render->send();
        } else {
            $this->handleNotFoundException($response);
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
     * @param TemplateAbstract $template
     *
     * @return void
     */
    protected function handleWebTemplate(TemplateAbstract $template)
    {
        $template->setCacheDir($this->getTempDir() . DIRECTORY_SEPARATOR . 'cache');
        if (is_null($template->getTemplateDir()) && isset($this->_templateDir)) {
            $template->setTemplateDir($this->_templateDir);
        }
        $template->setDev($this->isDev);
        // merge the controller parameters
        $controllerParams = $this->controller->getControllerParameters();
        if (is_array($controllerParams)) {
            $templateParams = array_merge(
                $this->controller->getParameters(),
                $controllerParams
            );
        }
        // and merge with any pre-existing template parameters
        $templateParams = array_merge($templateParams, $template->getParameters());
        // set the new params array as the template parameters
        $template->setParameters($templateParams);
        // prepare the template render
        $template->init();
        $response = $template->getWebResponse();
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        }
    }


    /**
     * Display a 404 error or similar
     *
     * @param mixed $response any response we can work with
     *
     * @throws \Synergy\Exception\NotFoundException
     */
    protected function handleNotFoundException($response = null)
    {
        $template = new HtmlTemplate();
        $template->setTemplateDir(SYNERGY_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . '_synergy_');
        $template->setTemplateFile('404.html');
        $template->init();
        $response = $template->getWebResponse();
        $response->setStatusCode(404);
        if ($response instanceof WebResponse) {
            $this->handleWebResponse($response);
        } else {
            throw new NotFoundException(
                'Unable to match Request to a Response via a Route'
            );
        }
    }


    /**
     * Name of the chosen controller class
     *
     * @return \Mobile_Detect
     */
    public function getControllerName()
    {
        return $this->controller->getClassName();
    }


    /**
     * Value of member _controller
     *
     * @return \Synergy\Controller\ControllerEntity value of member
     */
    public function getController()
    {
        return $this->controller;
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
        if (substr($dir, 0, 1) != DIRECTORY_SEPARATOR && defined('SYNERGY_ROOT_DIR')) {
            $dir = SYNERGY_ROOT_DIR . DIRECTORY_SEPARATOR . $dir;
        }
        Logger::debug(
            'Trying template_dir: '.$dir
        );
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
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


    /**
     * Replaces any variable tags (eg %app_dir%) in the value
     *
     * @param string $option_value look for variables to substitute in this string
     *
     * @return string
     */
    protected function replaceOptionVariables($option_value)
    {
        if (isset($this->_templateDir)) {
            $option_value = preg_replace('/%template_dir%/', $this->_templateDir, $option_value);
        }

        // run any parent substitutions
        $option_value = parent::replaceOptionVariables($option_value);

        return $option_value;
    }


    /**
     * Attempts to locate our template directory
     *
     * @param string $baseDir directory to search down from
     *
     * @return bool false if nothing found
     */
    protected function searchTemplateDir($baseDir = null)
    {
        if (!is_string($baseDir) && defined('SYNERGY_ROOT_DIR')) {
            $baseDir = SYNERGY_ROOT_DIR;
        }
        if (is_string($baseDir)) {
            $testfile = $baseDir . DIRECTORY_SEPARATOR . 'templates';
            if ($this->isValidDirectory($testfile)) {
                $this->setTemplateDir($testfile);
                return true;
            }
            $testfile = $baseDir . DIRECTORY_SEPARATOR . 'Templates';
            if ($this->isValidDirectory($testfile)) {
                $this->setTemplateDir($testfile);
                return true;
            }
        }

        // test from 1 level up from script path
        $testfile = dirname(dirname($_SERVER["SCRIPT_FILENAME"])) . DIRECTORY_SEPARATOR . 'templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }
        $testfile = dirname(dirname($_SERVER["SCRIPT_FILENAME"])) . DIRECTORY_SEPARATOR . 'Templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }

        // test in app dir
        if (isset($this->app_dir)) {
            $testfile = $this->app_dir . DIRECTORY_SEPARATOR . 'templates';
            if ($this->isValidDirectory($testfile)) {
                $this->setTemplateDir($testfile);
                return true;
            }
            $testfile = $this->app_dir . DIRECTORY_SEPARATOR . 'Templates';
            if ($this->isValidDirectory($testfile)) {
                $this->setTemplateDir($testfile);
                return true;
            }
        }

        // test 3 levels up
        $testfile = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }
        $testfile = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'Templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }

        // test 6 levels up
        $testfile = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . DIRECTORY_SEPARATOR . 'templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }
        $testfile = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . DIRECTORY_SEPARATOR . 'Templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }

        // fall through :

        // test from script path
        $testfile = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . 'templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }
        $testfile = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . 'Templates';
        if ($this->isValidDirectory($testfile)) {
            $this->setTemplateDir($testfile);
            return true;
        }

        return false;
    }

}
