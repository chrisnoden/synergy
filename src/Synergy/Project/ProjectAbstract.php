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
use Synergy\AutoLoader\SplClassLoader;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Object;
use Synergy\Project;
use Synergy\Tools\Tools;

/**
 * Class ProjectAbstract
 *
 * @category Synergy\Project
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
abstract class ProjectAbstract extends Object
{

    /**
     * @var int utime of when the project object was instantiated
     */
    protected $timeStart;
    /**
     * @var string path to the app directory
     */
    protected $appDir;
    /**
     * @var string path where our working temp folder (read-writable) exists
     */
    protected $tempDir;
    /**
     * @var string filename of the main config file
     */
    protected $configFilename;
    /**
     * @var bool is this a dev project
     */
    protected $isDev = false;


    /**
     * Important to execute this in any child classes
     * preferably BEFORE running any of your own code
     */
    public function __construct()
    {
        $this->timeStart = microtime(true);

        // Set our random logging ID using the log scope
        if (method_exists(Project::getLogger(), 'setTag')) {
            /** @noinspection PhpUndefinedMethodInspection */
            Project::getLogger()->setTag(Tools::randomString(6, '0123456789ABCDEF'));
        }
    }


    /**
     * destructor - cleans up where necessary
     */
    public function __destruct()
    {
        $synergy_endTime = microtime(true);
        $synergy_execTime = number_format($synergy_endTime - $this->timeStart, 4);
        if (Project::isDev()) {
            Project::getLogger()->log(
                LogLevel::INFO,
                "Execution time=$synergy_execTime seconds"
            );
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
     * @param string $dir path where we wish to store any project temp files/caches
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setTempDir($dir)
    {
        if (!is_dir($dir) && !Tools::mkdir($dir, true)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
            );
        } else if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not writable", $dir)
            );
        } else {
            $this->tempDir = $dir;
        }
    }


    /**
     * temp directory
     *
     * @return string temp dir
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }


    /**
     * directory where the app data lives
     *
     * @param string $dir directory where the app data live
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setAppDir($dir)
    {
        if (!is_dir($dir) && !Tools::mkdir($dir, true)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
            );
        } else if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not writable", $dir)
            );
        } else {
            $this->appDir = $dir;
            $classLoader = new SplClassLoader($dir);
            $classLoader->register();
        }
    }


    /**
     * directory where app data lives
     *
     * @return string directory where app data lives
     */
    public function getAppDir()
    {
        return $this->appDir;
    }


    /**
     * filename of the main config file
     *
     * @param string $filename filename of the main config file
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setConfigFilename($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                sprintf("Missing file %s", $filename)
            );
        } else if (!is_readable($filename)) {
            throw new InvalidArgumentException(
                sprintf("File %s not readable", $filename)
            );
        } else {
            $this->configFilename = $filename;
        }
    }


    /**
     * filename of the main config file
     *
     * @return string filename of the main config file
     */
    public function getConfigFilename()
    {
        return $this->configFilename;
    }


    /**
     * is this a dev project
     *
     * @param bool $isDev is this a dev project
     *
     * @return void
     */
    public function setDev($isDev)
    {
        $this->isDev = $isDev;
        Project::setDev($isDev);
    }

}
