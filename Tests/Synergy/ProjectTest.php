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
namespace Synergy\Tests;

use Synergy\Project;
use Synergy\Project\ProjectType;

class ProjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Basic test of Object
     */
    public function testSingletonObject()
    {
        $obj = Project::getInstance();
        $this->assertInstanceOf('Synergy\Singleton', $obj);
        $this->assertInstanceOf('Synergy\Project', $obj);
    }


    /**
     * Are the right Project constants defined
     * @throws \Exception
     */
    public function testProjectTypeConstants()
    {
        $t = ProjectType::getInstance();
        $r = new \ReflectionObject($t);
        $aConstants = $r->getConstants();

        if (count($aConstants) != 3) {
            throw new \Exception("Should only be 3 Project constants in Synergy\\Project");
        }
        if (!isset($aConstants['WEB']) || $aConstants['WEB'] != 'web') {
            throw new \Exception("Missing WEB project (const) type in Synergy\\Project");
        }
        if (!isset($aConstants['CLI']) || $aConstants['CLI'] != 'cli') {
            throw new \Exception("Missing CLI project (const) type in Synergy\\Project");
        }
        if (!isset($aConstants['DAEMON']) || $aConstants['DAEMON'] != 'daemon') {
            throw new \Exception("Missing DAEMON project (const) type in Synergy\\Project");
        }
    }


    /**
     * Test that Project::Init() resets the stored parameters
     */
    public function testProjectInit()
    {
        Project::init();
        Project::setName('Test Project');
        Project::setType(ProjectType::WEB);
        Project::setOptions(array(1 => true, 2 => false, 3 => 'no'));

        $this->assertEquals('Test Project', Project::getName());
        $this->assertEquals(ProjectType::WEB, Project::getType());
        $this->assertArrayHasKey(1, Project::getOptions());
        $this->assertArrayHasKey(2, Project::getOptions());
        $this->assertArrayHasKey(3, Project::getOptions());

        Project::init();
        $this->assertNull(Project::getName());
        $this->assertNull(Project::getType());
        $this->assertEmpty(Project::getOptions());
    }

}