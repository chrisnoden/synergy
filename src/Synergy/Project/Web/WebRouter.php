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

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Synergy\Logger\Logger;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Synergy\Router\RouterAbstract;

/**
 * Class WebRouter
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebRouter extends RouterAbstract
{

    /**
     * @var WebRequest $request
     */
    protected $request;


    /**
     * Try to match the WebRequest to a Route
     *
     * @param WebRequest $request WebRequest object
     */
    public function __construct(WebRequest $request)
    {
        $this->request = $request;
    }


    /**
     * @return void
     */
    protected function matchRoute()
    {
        if (isset($this->routeCollection)) {
            $context = new WebRequestContext();
            $context->fromWebRequest($this->request);

            $matcher = new RouteMatcher($this->routeCollection, $context);
            try {
                Logger::debug(
                    'Looking for route match: '.$this->request->getPathInfo()
                );
                $parameters = $matcher->match($this->request->getPathInfo());
                if (is_array($matcher->getRouteOption('parameters'))) {
                    $this->parameters = array_merge($this->parameters, $matcher->getRouteOption('parameters'));
                }
            } catch (ResourceNotFoundException $ex) {
                // Use our DefaultController
                $parameters = $this->getDefaultController();
            }
        } else {
            // no route to match against, so use the DefaultController
            $parameters = $this->getDefaultController();
        }

        if (!isset($parameters)) {
            Logger::alert('Route not found');
        } else {
            $this->parseRouteParameters($parameters);
            Logger::debug(
                'Route matched: ' . $this->route
            );
        }
    }


    /**
     * controller defined in the successful route
     *
     * @return ControllerEntity the controller defined by the route
     */
    public function getController()
    {
        $this->controller->setParameters($this->parameters);

        return $this->controller;
    }
}