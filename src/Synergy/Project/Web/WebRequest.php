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

/**
 * Class WebRequest
 * Extends the Symfony Request class so we can integrate device detection
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebRequest extends Request
{

    /**
     * @var \Mobile_Detect mobile device type
     */
    private $_device;


    /**
     * Detect the browser type (Mobile, iOS, Android)
     * using the mobiledetect\Mobile_Detect library
     *
     * @return void
     */
    public function detectDevice()
    {
        if (class_exists('\Mobile_Detect')) {
            $this->_device = new \Mobile_Detect();
        }
    }


    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return Request A new request
     *
     * @api
     */
    public static function createFromGlobals()
    {
        $request = new static($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        $request->detectDevice();

        return $request;
    }


    /**
     * Mobile Device info from the mobiledetect\Mobile_Detect library
     *
     * @param \Mobile_Detect $device
     */
    public function setDevice(\Mobile_Detect $device)
    {
        $this->_device = $device;
    }


    /**
     * Device object
     *
     * @return \Mobile_Detect Device object
     */
    public function getDevice()
    {
        return $this->_device;
    }

}