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

use Synergy\Controller\Parser;
use Synergy\Exception\SynergyException;
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

        if (!is_null($request)) {
            $this->request = $request;
        }
        $this->parameters = $parameters;

        parent::__construct();
    }


    /**
     * Run our CLI Project
     *
     * @param string $action     class and method to launch
     * @param array  $parameters parameters to pass to the method
     *
     * @return void
     */
    public function launch($action = null, array $parameters = array())
    {
//        if (!is_null($action)) {
//            $controllerName = $parser->getControllerName();
//            $methodName = $parser->getMethodName();
//            $controller = new $controllerName();
//            $controller->{$methodName}();
//        }


    }

}