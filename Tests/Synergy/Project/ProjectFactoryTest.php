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