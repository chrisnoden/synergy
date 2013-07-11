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

namespace Synergy\Tests\Controller;

use Synergy\Controller\ControllerEntity;

/**
 * Class ControllerEntityTest
 *
 * @category Synergy\Tests\Controller
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class ControllerEntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ControllerEntity
     */
    private static $_obj;

    public function testObjectInstantiation()
    {
        self::$_obj = new ControllerEntity();
        $this->assertInstanceOf('\Synergy\Controller\ControllerEntity', self::$_obj);
        self::$_obj->setController('Test\\TestController');
        $this->assertEquals('Test\TestController', self::$_obj->getClassName());
        $this->assertEquals('defaultAction', self::$_obj->getMethodName());
    }

    public function testControllerAction()
    {
        $this->setExpectedException('\Synergy\Exception\InvalidArgumentException', 'getParameters() method in Test\TestController must return array');
        self::$_obj->callControllerAction();
    }


}