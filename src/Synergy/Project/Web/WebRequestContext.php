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

use Symfony\Component\Routing\RequestContext;

/**
 * Class WebRequestContext
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebRequestContext extends RequestContext
{

    /**
     * @var \Mobile_Detect
     */
    protected $device;


    /**
     * @param WebRequest $request Object containing the HTTP request
     */
    public function fromWebRequest(WebRequest $request)
    {
        $this->setBaseUrl($request->getBaseUrl());
        $this->setPathInfo($request->getPathInfo());
        $this->setMethod($request->getMethod());
        $this->setHost($request->getHost());
        $this->setScheme($request->getScheme());
        $this->setHttpPort($request->isSecure() ? $this->getHttpPort : $request->getPort());
        $this->setHttpsPort($request->isSecure() ? $request->getPort() : $this->getHttpsPort());

        if (class_exists('\Mobile_Detect') && $request->getDevice() instanceof \Mobile_Detect) {
            $this->setDevice($request->getDevice());
        }
    }


    /**
     * @param \Mobile_Detect $device
     */
    public function setDevice(\Mobile_Detect $device)
    {
        $this->device = $device;
    }


    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }


}