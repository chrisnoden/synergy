<?php
/**
 * Created by Chris Noden using PhpStorm.
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
 * @category  Class
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Synergy\Project\Web;

/**
 * Class WebTestAbstract
 *
 * @category Synergy\Tests\Project\Web
 * @package  synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebTestAbstract extends \PHPUnit_Framework_TestCase
{

    /**
     * @param string $stubUrl eg 'FileSessionHandlerTest.php'
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function fetchTestResponse($stubUrl)
    {
        if (defined('SYNERGY_WEBTEST_BASEURL')) {
            $url = SYNERGY_WEBTEST_BASEURL . '/' . $stubUrl;
        } else {
            throw new \InvalidArgumentException('Must define SYNERGY_WEBTEST_BASEURL constant for Web Tests');
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER         => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => false, // follow redirects
            CURLOPT_ENCODING       => "", // handle compressed
            CURLOPT_USERAGENT      => "test", // who am i
            CURLOPT_AUTOREFERER    => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect
            CURLOPT_TIMEOUT        => 10, // timeout on response
        ); // stop after 10 redirects

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content  = curl_exec($ch);

        curl_close($ch);

        return $content;
    }
}
