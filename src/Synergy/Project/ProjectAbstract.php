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

namespace Synergy\Project;

use Psr\Log\LogLevel;
use Synergy\Object;
use Synergy\Project;
use Synergy\Tools\Tools;

/**
 * All Project classes must inherit this class
 */
abstract class ProjectAbstract extends Object
{

    /**
     * @var int utime of when the project object was instantiated
     */
    protected $timeStart;
    /**
     * @var string the path to the SAL platform directory
     */
    protected $platformPath;
    /**
     * @var @string path where our working temp folder (read-writable) exists
     */
    protected $tempFolderPath;


    /**
     * Important to execute this in any child classes
     * preferably BEFORE running any of your own code
     */
    public function __construct()
    {
        $this->timeStart = microtime(true);

        // Set our random logging ID using the log scope
        if (method_exists(Project::getLogger(), 'setTag')) {
            Project::getLogger()->setTag(Tools::randomString(6, '0123456789ABCDEF'));
        }

//        if (Project::getProjectConfigFilename()) {
//            Config::loadConfig();
//        } else {
//            throw new SynergyException("You must set the Project Config filename");
//        }
    }


    /**
     * destructor - cleans up where necessary
     */
    public function __destruct()
    {
        $synergy_endTime = microtime(true);
        $synergy_execTime = number_format($synergy_endTime - $this->timeStart, 4);
        if (Project::isDev()) {
            Project::getLogger()->log(LogLevel::INFO, "Execution time=$synergy_execTime seconds");
        }
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return Project::getName();
    }


    /**
     * Launch your main project code
     *
     * @return void
     */
    abstract public function launch();


    /**
     * create/test our preferred temp folder structure
     *
     * @param $path string path where we wish to store any project temp files/caches
     */
    protected function initTempFolder($path)
    {
        $documentRoot = $this->initProjectDir($path);
        if ($documentRoot) {
            $this->tempFolderPath = $documentRoot;
            return;
        }
        // The preferred temp folder location failed - try a fallback
        $documentRoot = $this->initProjectDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . escapeshellarg(Project::getName()));
        if ($documentRoot) {
            $this->tempFolderPath = $documentRoot;
            return;
        }
    }


    /**
     * Creates a folder if it doesn't exist (plus the parent folders)
     * Optionally tests it (even if it already exists) for read & write permissions by the platform
     *
     * @param $path string folder we wish tested/created
     * @param $test bool default=true test the folder for write permissions
     * @return bool true if created/exists and is read/writeable
     */
    protected function mkdir($path, $test = true)
    {
        if (!file_exists($path) || !is_dir($path)) {
            @mkdir($path, 0770, true);
        }
        // Test the folder for suitability
        if (file_exists($path) && is_readable($path) && is_dir($path)) {
            // Try to save something in the path
            @touch($path . DIRECTORY_SEPARATOR . 'testfile');
            if (file_exists($path . DIRECTORY_SEPARATOR . 'testfile')) {
                unlink($path . DIRECTORY_SEPARATOR . 'testfile');
                return true;
            }
        }
    }


}
