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

namespace Synergy\Tests\Project;

use Synergy\Project\ProjectFactory;
use Synergy\Project\ProjectType;
use Synergy\Logger\FileLogger;

class ProjectFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var static
     */
    private static $_logFile;


    public static function setUpBeforeClass()
    {
        self::$_logFile = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'test.log';
    }


    public static function tearDownAfterClass()
    {
        if (file_exists(self::$_logFile)) {
            unlink(self::$_logFile);
        }
    }


    /**
     * Try creating a new Web Project
     */
    public function testWebLaunch()
    {
        $obj = ProjectFactory::build(
            'Test Project',
            ProjectType::WEB,
            new FileLogger(self::$_logFile)
        );
        $this->assertInstanceOf('Synergy\Project\Web\WebProject', $obj);
        $this->assertInstanceOf('Synergy\Project\ProjectAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
        $this->assertEquals('Test Project', $obj);
    }


    /**
     * Try creating a new CLI Project
     */
    public function testCliLaunch()
    {
        $obj = ProjectFactory::build(
            'Test Project',
            ProjectType::CLI,
            new FileLogger(self::$_logFile)
        );
        $this->assertInstanceOf('Synergy\Project\Cli\CliProject', $obj);
        $this->assertInstanceOf('Synergy\Project\ProjectAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
        $this->assertEquals('Test Project', $obj);
    }


    /**
     * Try creating a new Daemon Project
     */
    public function testDaemonLaunch()
    {
        $obj = ProjectFactory::build(
            'Test Project',
            ProjectType::DAEMON,
            new FileLogger(self::$_logFile)
        );
        $this->assertInstanceOf('Synergy\Project\Daemon\DaemonProject', $obj);
        $this->assertInstanceOf('Synergy\Project\ProjectAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
        $this->assertEquals('Test Project', $obj);
    }

}