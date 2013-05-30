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
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Route;
use Synergy\Logger\Logger;

/**
 * Class RouteMatcher
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class RouteMatcher extends UrlMatcher
{

    /**
     * Tries to match a URL with a set of routes.
     *
     * @param string          $pathinfo The path info to be parsed
     * @param RouteCollection $routes   The set of routes
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException method is not allowed
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
        $context_params = $this->context->getParameters();

        /**
         * @var $route \Symfony\Component\Routing\Route
         */
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();

            // check the static prefix of the URL first.
            // Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix()
                && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())
            ) {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                continue;
            }

            $hostMatches = array();
            if ($compiledRoute->getHostRegex()
                && !preg_match(
                    $compiledRoute->getHostRegex(),
                    $this->context->getHost(),
                    $hostMatches
                )
            ) {
                continue;
            }

            // check HTTP method requirement
            if ($req = $route->getRequirement('_method')) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->context->getMethod()) {
                    $method = 'GET';
                }

                if (!in_array($method, $req = explode('|', strtoupper($req)))) {
                    $this->allow = array_merge($this->allow, $req);

                    continue;
                }
            }

            // check context parameters
            if (is_array($context_params)) {
                foreach ($context_params AS $paramName=>$paramValue)
                {
                    if ($route->getOption($paramName)
                        && $route->getOption($paramName) != $paramValue) {
                        continue(2);
                    }
                }
            }

            $status = $this->handleRouteRequirements($pathinfo, $name, $route);

            if (self::ROUTE_MATCH === $status[0]) {
                return $status[1];
            }

            if (self::REQUIREMENT_MISMATCH === $status[0]) {
                continue;
            }

            return $this->getAttributes($route, $name, array_replace($matches, $hostMatches));
        }
    }

}
