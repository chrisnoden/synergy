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
 * Extends the Symfony UrlMatcher component so we can look for
 * device options (eg device == phone)
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
     * @var WebRequestContext
     */
    protected $context;


    /**
     * Tries to match a URL with a set of routes.
     * Will always return the first route that matches.
     *
     * @param string          $pathinfo The path info to be parsed
     * @param RouteCollection $routes   The set of routes
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException method is not allowed
     */
    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
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

            // check device
            if (class_exists('\Mobile_Detect') && $this->context->getDevice() instanceof \Mobile_Detect) {
                /**
                 * @var $device \Mobile_Detect
                 */
                $device     = $this->context->getDevice();
                $deviceType = ($device->isMobile()
                    ? ($device->isTablet() ? 'tablet' : 'mobile')
                    : 'computer');

                if ($route->getOption('device')
                    && $route->getOption('device') != $deviceType
                ) {
                    continue;
                }

                /**
                 * Test for a specific mobile OS
                 */
                if ($routeOS = $route->getOption('os')) {
                    $deviceOS = $this->matchDeviceOS($routeOS, $device);
                    if ($deviceOS) {
                        Logger::info("Matched RouteOS: " . $deviceOS);
                    } else {
                        continue;
                    }
                }

                /**
                 * Test for a specific mobile/tablet type (brand)
                 * eg iPhone, BlackBerry, HTC, Dell, etc
                 */
                if ($device->isMobile() && !$device->isTablet() && $routeType = $route->getOption('type')) {
                    $phoneType = $this->matchPhoneType($routeType, $device);
                    if ($phoneType) {
                        Logger::info("Matched RouteType: " . $phoneType);
                    } else {
                        continue;
                    }
                } else if ($device->isTablet() && $routeType = $route->getOption('type')) {
                    $tabletType = $this->matchTabletType($routeType, $device);
                    if ($tabletType) {
                        Logger::info("Matched RouteType: " . $tabletType);
                    } else {
                        continue;
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

            return $this->getAttributes(
                $route,
                $name,
                array_replace($matches, $hostMatches)
            );
        }
    }


    /**
     * Can we match the OS in the route to the device
     *
     * @param string         $routeOS os option in the route
     * @param \Mobile_Detect $device  device object from the WebRequest
     *
     * @return bool|string matched OS
     */
    protected function matchDeviceOS($routeOS, \Mobile_Detect $device)
    {
        $routeOS = strtolower($routeOS);
        if (substr($routeOS, -2) != 'os') {
            $routeOS .= 'os';
        }

        $aOperatingSystems
            = $this->rejiggerMobileParameterArray($device->getOperatingSystems());
        if (!isset($aOperatingSystems[$routeOS])
            || !$device->is($aOperatingSystems[$routeOS])
        ) {
            return false;
        }
        return $aOperatingSystems[$routeOS];
    }


    /**
     * Can we match the Mobile Device Type in the route to the device
     *
     * @param string         $routeValue type option in the route
     * @param \Mobile_Detect $device     device object from the WebRequest
     *
     * @return bool|string matched OS
     */
    protected function matchPhoneType($routeValue, \Mobile_Detect $device)
    {
        $routeValue = strtolower($routeValue);

        $aParameters
            = $this->rejiggerMobileParameterArray($device->getPhoneDevices());
        if (isset($aParameters[$routeValue])) {
            $isType = 'is' . $aParameters[$routeValue];
            if ($device->$isType()) {
                return $aParameters[$routeValue];
            }
        }
        return false;
    }


    /**
     * Can we match the Tablet Device Type in the route to the device
     *
     * @param string         $routeValue type option in the route
     * @param \Mobile_Detect $device     device object from the WebRequest
     *
     * @return bool|string matched OS
     */
    protected function matchTabletType($routeValue, \Mobile_Detect $device)
    {
        $routeValue = strtolower($routeValue);

        $aParameters
            = $this->rejiggerMobileParameterArray($device->getTabletDevices());
        if (isset($aParameters[$routeValue])) {
            $isType = 'is' . $aParameters[$routeValue];
            if ($device->$isType()) {
                return $aParameters[$routeValue];
            }
        }
        return false;
    }


    /**
     * manipulate the possible operating systems
     * to create an associative array we can search against
     *
     * @param array $origArray parameter array from \Mobile_Detect library
     *
     * @return array
     */
    protected function rejiggerMobileParameterArray(array $origArray)
    {
        $aKeys = array_keys($origArray);
        array_walk(
            $aKeys,
            function (&$n) {
                $n = strtolower($n);
            }
        );
        $aValues = array_keys($origArray);
        return (array_combine($aKeys, $aValues));
    }

}
