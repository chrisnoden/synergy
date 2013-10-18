<?php
/**
 * Created by Chris Noden using PhpStorm.
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
 * @category  Unit Test
 * @package   Synergy MVC Library
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

namespace Synergy\Tests\View;

use Synergy\View\SmartyTemplate;

class SmartyTemplateTest extends \PHPUnit_Framework_TestCase
{

    public function testBasicObject()
    {
        $obj = new SmartyTemplate();
        $obj->setCacheDir('/tmp');
        $obj->setProjectTemplateDir(SYNERGY_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'templates');
        $obj->setTemplateDir('smarty');
        $obj->setTemplateFile('index.html.tpl');
        $obj->init();
        $this->assertInstanceOf('Synergy\View\SmartyTemplate', $obj);
        $this->assertInstanceOf('Synergy\View\TemplateAbstract', $obj);
    }


    public function testRelativeDirFails()
    {
        $obj = new SmartyTemplate();
        $obj->setCacheDir('/tmp');
        $obj->setProjectTemplateDir(SYNERGY_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'templates');
        $obj->setTemplateFile('index.html.tpl');
        $this->setExpectedException('Synergy\Exception\SynergyException');
        $obj->init();
    }
}
