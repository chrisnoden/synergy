<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 * 
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Tests;

use Synergy\Logger\FileLogger;
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
            throw new \Exception("Should be 3 Project constants in Synergy\\Project");
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



}