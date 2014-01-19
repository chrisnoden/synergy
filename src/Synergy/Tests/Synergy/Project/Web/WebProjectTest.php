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
use Synergy\Project\Web\WebRequest;
use Synergy\Project;

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
        Project::setName('TestProject');
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
        $request = WebRequest::create('/');
        $obj = new WebProject($request);
        $this->hasOutput();
        $obj->run();
        $this->assertEquals(
            'Synergy\Controller\DefaultController',
            $obj->getControllerName()
        );
    }


    public function testCacheDir()
    {
        $obj = new WebProject();
        $tempDir = SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'testcache';
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
        $obj->setTempDir($tempDir);
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        } else {
            $this->fail('Cache dir not created');
        }
    }


    public function testSynergyCache()
    {
        $request = WebRequest::create(
            SYNERGY_WEBTEST_BASEURL.'/smarty',
            'GET'
        );
        $request->overrideGlobals();
        $this->hasOutput();
        $obj = new WebProject($request);
        $tempDir = SYNERGY_TEST_FILES_DIR . DIRECTORY_SEPARATOR . 'testcache';
        $cacheDir = $tempDir.DIRECTORY_SEPARATOR.'synergy';
        $obj->setTempDir($tempDir);
        if (is_dir($cacheDir)) {
            rmdir($cacheDir);
        }
        $obj->setDeliverResponse(false);
        $obj->run();

        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . md5($request->getUri()) . '.syn';
        if (!file_exists($cacheFile) && !file_exists($cacheFile.'.gz')) {
            $this->fail('Failed to detect synergy cache file: '.$cacheFile);
        }
    }


    public function testInternalSynergyRoute()
    {
        // This GET request should fail
        $request = WebRequest::create(
            SYNERGY_WEBTEST_BASEURL.'/_synergy_/css/bootstrap.min.css',
            'GET'
        );
        $request->overrideGlobals();
        $this->hasOutput();
        $obj = new WebProject($request);
        $obj->setDeliverResponse(false);
        $obj->run();
        $response = $obj->getResponse();
        $this->assertInstanceOf('Synergy\Project\Web\WebAsset', $response);
        $filename = $response->getFilename();
        $filename = str_replace(dirname(SYNERGY_ROOT_DIR), '', $filename);
        $this->assertSame('/View/_synergy_/css/bootstrap.min.css', $filename);
    }

}