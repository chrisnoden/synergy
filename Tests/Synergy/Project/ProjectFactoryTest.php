<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 * 
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Tests\Project;

use Synergy\Project\ProjectFactory;
use Synergy\Project\ProjectType;
use Synergy\Logger\FileLogger;

class ProjectFactoryTest extends \PHPUnit_Framework_TestCase
{


    /**
     * Try creating a new Web Project
     */
    public function testWebLaunch()
    {
        $obj = ProjectFactory::build(
            'Test Project',
            ProjectType::WEB,
            new FileLogger(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'test.log')
        );
        $this->assertInstanceOf('Synergy\Project\Web\WebProject', $obj);
        $this->assertInstanceOf('Synergy\Project\ProjectAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
        $this->assertEquals('Test Project', $obj);
    }
}