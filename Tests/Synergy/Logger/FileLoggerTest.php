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
 * @category  Test
 * @package   Synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Logger;

use Synergy\Logger\FileLogger;

/**
 * Class FileLoggerTest
 *
 * @package Synergy\Tests\Logger
 */
class FileLoggerTest extends \PHPUnit_Framework_TestCase
{

    private $_testFileName;
    private $_invalidFileName;


    public function setUp() {
        $this->_testFileName = '/private/tmp/test.log';
        $this->_invalidFileName = DIRECTORY_SEPARATOR . 'invalidpath' .
            DIRECTORY_SEPARATOR . 'file.log';
    }


    public function tearDown()
    {
        if (file_exists($this->_testFileName)) {
            unlink($this->_testFileName);
        }
        if (file_exists($this->_invalidFileName)) {
            unlink($this->_invalidFileName);
        }
    }


    public function testObject()
    {
        $obj = new FileLogger();
        $this->assertInstanceOf('Synergy\Logger\FileLogger', $obj);
        $this->assertInstanceOf('Synergy\Logger\LoggerAbstract', $obj);
        $this->assertInstanceOf('Synergy\Logger\LoggerInterface', $obj);
    }


    /**
     * writing messages to the file
     */
    public function testWrite()
    {
        $obj = new FileLogger();
        $obj->setFilename($this->_testFileName);
        $obj->error("Test message");
        $this->assertFileExists($this->_testFileName);
        $this->assertStringEqualsFile($this->_testFileName, "Test message\n");
    }


    /**
     * invalid filename
     */
    public function testInvalidFilename()
    {
        $this->setExpectedException(
            'Synergy\Exception\InvalidArgumentException',
            'filename must be an absolute filename in a writeable directory'
        );
        $obj = new FileLogger();
        $obj->setFilename($this->_invalidFileName);
    }

}
