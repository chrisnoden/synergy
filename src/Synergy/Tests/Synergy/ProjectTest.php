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