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
declare(ticks = 1);

namespace Synergy\Project\Cli;

use Synergy\Exception\SynergyException;
use Synergy\Object;
use Synergy\Project\ProjectAbstract;

/**
 * Class CliProject
 *
 * @category Synergy\Project\Cli
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class CliProject extends ProjectAbstract
{

    /**
     * @var string
     */
    protected $request;
    /**
     * @var array
     */
    protected $parameters = array();
    /**
     * @var string
     */
    protected $rawArguments = '';
    /**
     * @var array arguments for the Symfony CliProject
     */
    protected $sysArgs = array();


    /**
     * Instantiate a new CliProject object
     *
     * @param null  $request    the action request notation
     * @param array $parameters parameters to pass to the action
     *
     * @throws SynergyException
     */
    public function __construct($request = null, array $parameters = array())
    {
        register_tick_function(array(&$this, "checkExit"));

        // Check this is coming from the CLI
        if (PHP_SAPI !== 'cli') {
            throw new SynergyException(
                sprintf('%s must be run from command line project', __CLASS__)
            );
        }

        // Store or build the request
        $this->parameters = $parameters;
        if (!is_null($request)) {
            $this->request = $request;
        } else {
            $args = new ArgumentParser();
            $result = $args->parseArguments();
            $this->request = $result['request'];
            $this->rawArguments = $result['arguments'];
        }

        $this->registerSignalHandler();

        parent::__construct();
    }


    /**
     * Registers a signal handler method to respond
     * to any kill signals
     */
    protected  function registerSignalHandler() {
        pcntl_signal(SIGTERM, array(&$this,"handleSignals"));
        pcntl_signal(SIGINT, array(&$this,"handleSignals"));
    }


    /**
     * Respond to a signal
     *
     * @param $signal
     */
    public function handleSignals($signal)
    {
        if (!SignalHandler::$blockExit) {
            exit;
        } else {
            SignalHandler::$forceExit = true;
        }
    }


    /**
     * If an exit has been requested and we have removed the block
     * then we can now exit
     */
    public function checkExit()
    {
        if (!SignalHandler::$blockExit && SignalHandler::$forceExit) {
            exit;
        }
    }


    /**
     * Run our CLI Project
     *
     * @return void
     */
    protected function launch()
    {
        $router = new CliRouter($this->request);
        $router->match();

        /**
         * Get the ControllerEntity
         */
        try {
            $this->controller = $router->getController();
            // pass the parameters
            $this->controller->setParameters($this->parameters);
            // Call the action
            $response = $this->controller->callControllerAction();
        }
        catch (\Exception $ex) {
            var_dump($ex);
        }

        if (is_string($response)) {
            \Cli\line($response);
        }

    }


}