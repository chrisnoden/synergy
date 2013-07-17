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
 * @package   Synergy MVC Library
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Logger;

use Synergy\Logger\CliLogger;
use Synergy\Logger\FileLogger;
use Synergy\Logger\SynergyLogger;

/**
 * Class SynergyLoggerTest
 *
 * @category Synergy\Tests\Logger
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SynergyLoggerTest extends \PHPUnit_Framework_TestCase
{

    private $_testFileName;


    public function setUp() {
        $this->_testFileName = '/private/tmp/test.log';
    }


    public function tearDown()
    {
        if (file_exists($this->_testFileName)) {
            unlink($this->_testFileName);
        }
    }


    public function testObjectInstantiation()
    {
        $obj = new SynergyLogger();
        $this->assertInstanceOf('Synergy\Logger\SynergyLogger', $obj);
    }


    public function testLoggerArray()
    {
        $obj = new SynergyLogger();
        $arr = $obj->getLoggers();
        if (!empty($arr)) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'SynergyLogger::_aLoggers array in SynergyLogger is not empty on instantiation'
            );
        }
        $obj->addCliLogger();
        $arr = $obj->getLoggers();
        if (empty($arr) || count($arr) != 1 || !$arr[0] instanceof CliLogger) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'SynergyLogger::_aLoggers array should have one CliLogger item only'
            );
        }
        $obj->addFileLogger($this->_testFileName);
        $arr = $obj->getLoggers();
        if (empty($arr) || count($arr) != 2 || !$arr[1] instanceof FileLogger) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'SynergyLogger::_aLoggers array should have one FileLogger'
            );
        }
    }

}