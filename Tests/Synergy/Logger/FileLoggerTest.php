<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * PHP version 5
 *
 * @category  Project:Synergy
 * @package   Synergy
 * @author    Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 * @link      http://chrisnoden.com
 * @license   http://opensource.org/licenses/LGPL-3.0
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
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $obj);
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
