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
     * @var SignalHandler
     */
    protected $sigHandler;


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
            $result = $this->parseArgumentsForProjectParams();
            $this->request = $result['request'];
            $this->rawArguments = $result['arguments'];
        }

        parent::__construct();
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
        $this->controller = $router->getController();
        // Call the action
        $response = $this->controller->callControllerAction();

        if (is_string($response)) {
            \Cli\line($response);
        } else if ($response instanceof Object) {
            \Cli\line($response->__toString());
        }

    }


    /**
     * Parses out all the command line arguments to find the arguments
     * for the Symfony CliProject and the requested controller plus any
     * arguments to pass to the controller
     *
     * @return array associative array of the request and the raw arguments for it
     */
    protected function parseArgumentsForProjectParams()
    {
        /**
         * Store our parsed results here
         */
        $result = array();

        $request = null;
        $requestArgs = array();
        $systemArgs = array();
        $phase = 1;
        $script_filename = strtolower($_SERVER['SCRIPT_FILENAME']);

        foreach ($_SERVER['argv'] AS $val)
        {
            switch ($phase)
            {
                case 1:
                    // look for our app/console script first
                    if (strtolower($val) == $script_filename) {
                        $phase = 2;
                        continue;
                    }
                    break;

                case 2:
                    // look for any args for Symfony
                    if (substr($val, 0, 1) == '-') {
                        $systemArgs[] = $val;
                    } else {
                        $phase = 3;
                        $request = $val;
                    }
                    break;

                case 3:
                    // look for any args for the request
                    $requestArgs[] = $val;

            }
        }

        $this->parseSystemArgs($systemArgs);

        $result = array(
            'request' => $request,
            'arguments' => join(' ', $requestArgs)
        );

        return $result;
    }


    protected function parseSystemArgs($systemArgs)
    {
        $this->sysArgs = $systemArgs;
    }


}