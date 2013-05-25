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
 * @package   Synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\Project\Web;

use Synergy\Project\Web\WebProject;

/**
 * Class WebProjectTest
 *
 * @category Synergy\Tests\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebProjectTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        \Synergy\Project::setName('TestProject');
    }


    public function testBaseObject()
    {
        $obj = new WebProject();
        $this->assertInstanceOf('Synergy\Project\Web\WebProject', $obj);
        $this->assertInstanceOf('Synergy\Project\ProjectAbstract', $obj);
        $this->assertInstanceOf('Synergy\Object', $obj);
        $this->assertEquals('TestProject', $obj->__toString());
    }


    public function testSessionAutoStartIsDisabled()
    {
        $obj = new WebProject();
        $this->assertEquals('0', ini_get('session.auto_start'));
    }


    public function testDefaultWebResponse()
    {
        $obj = new WebProject();
        $this->hasOutput();
        $obj->launch();
        $this->assertEquals('Synergy\Controller\DefaultController', $obj->getControllerName());
    }

}