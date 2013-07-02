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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Synergy\AutoLoader\SplClassLoader;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\SynergyException;
use Synergy\Logger\Logger;
use Synergy\Object;
use Synergy\Project;
use Synergy\Tools\Tools;
use Synergy\Controller\ControllerEntity;

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
     * @var string path to the app directory
     */
    protected $app_dir;
    /**
     * @var string path where our working temp folder (read-writable) exists
     */
    protected $temp_dir;
    /**
     * @var string filename of the main config file
     */
    protected $configFilename;
    /**
     * @var bool is this a dev project
     */
    protected $isDev = false;
    /**
     * @var ControllerEntity
     */
    protected $controller;
    /**
     * @var array project configuration settings
     */
    protected $options = array();


    /**
     * Important to execute this in any child classes
     * preferably BEFORE running any of your own code
     */
    public function __construct()
    {
        if (!defined('SYNERGY_LIBRARY_PATH')) {
            define('SYNERGY_LIBRARY_PATH', dirname(dirname(__FILE__)));
        }

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
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return Project::getName();
    }


    /**
     * Run our project
     *
     * @return void
     */
    public function run()
    {
        $this->loadBootstrap();

        $this->checkEnv();

        $this->launch();
    }


    /**
     * Include a project based bootstrap file
     *
     * @param string $filename bootstrap file to include
     */
    protected function loadBootstrap($filename = null)
    {
        if (is_null($filename)) {
            $filename = $this->app_dir . DIRECTORY_SEPARATOR . 'bootstrap.php';
        }
        if (file_exists($filename) && is_readable($filename)) {
            @include_once($filename);
            Logger::debug(
                sprintf('Bootstrap %s loaded',
                    str_ireplace(dirname($this->app_dir).DIRECTORY_SEPARATOR, '', $filename)
                )
            );
        }
    }


    /**
     * Launch your main project code
     *
     * @return void
     */
    abstract protected function launch();


    /**
     * Checks everything is good with our project before we run it
     *
     * @throws \Synergy\Exception\SynergyException
     */
    protected function checkEnv()
    {
        if (!defined('SYNERGY_ROOT_DIR')) {
            $testroot = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
            if ($this->isValidDirectory($testroot)) {
                define('SYNERGY_ROOT_DIR', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
            } else {
                throw new SynergyException(
                    'Please define your project root directory as SYNERGY_ROOT_DIR'
                );
            }
        } else if (!is_dir(SYNERGY_ROOT_DIR)) {
            throw new SynergyException(
                'SYNERGY_ROOT_DIR must point to a valid directory at the root of your project'
            );
        }

        if (!isset($this->app_dir) && !$this->searchAppDir()) {
            throw new SynergyException(
                'Unable to init Synergy library without an app directory'
            );
        }

        if (!isset($this->configFilename) && !$this->searchConfigFile()) {
            throw new SynergyException(
                'Unable to init Synergy library without config file'
            );
        }
    }


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
                sprintf("Invalid temp directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Temp Directory %s not readable", $dir)
            );
        } else if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Temp Directory %s not writable", $dir)
            );
        } else {
            $this->temp_dir = $dir;
        }
    }


    /**
     * temp directory
     *
     * @return string temp dir
     */
    public function getTempDir()
    {
        return $this->temp_dir;
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
                sprintf("Invalid App directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("App Directory %s not readable", $dir)
            );
        } else {
            $this->app_dir = $dir;
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
        return $this->app_dir;
    }


    /**
     * Attempts to locate our app directory
     *
     * @param string $baseDir directory to search down from
     *
     * @return bool false if nothing found
     */
    protected function searchAppDir($baseDir = null)
    {
        if (!is_string($baseDir) && defined('SYNERGY_WEB_ROOT')) {
                $baseDir = SYNERGY_ROOT_DIR;
        }
        if (!is_string($baseDir)) {

            // test from 1 level up from script path
            $testfile = dirname(dirname($_SERVER["SCRIPT_FILENAME"])) . DIRECTORY_SEPARATOR . 'app';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
            $testfile = dirname(dirname($_SERVER["SCRIPT_FILENAME"])) . DIRECTORY_SEPARATOR . 'App';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }

            // test 3 levels up
            $testfile = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'app';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
            $testfile = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'App';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }

            // test 6 levels up
            $testfile = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . DIRECTORY_SEPARATOR . 'app';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
            $testfile = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . DIRECTORY_SEPARATOR . 'App';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
        } else {
            $testfile = $baseDir . DIRECTORY_SEPARATOR . 'app';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
            $testfile = $baseDir . DIRECTORY_SEPARATOR . 'App';
            if ($this->isValidDirectory($testfile)) {
                $this->app_dir = $testfile;
                return true;
            }
        }

        // fall through :

        // test from script path
        $testfile = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . 'app';
        if ($this->isValidDirectory($testfile)) {
            $this->app_dir = $testfile;
            return true;
        }
        $testfile = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . 'App';
        if ($this->isValidDirectory($testfile)) {
            $this->app_dir = $testfile;
            return true;
        }

        return false;
    }


    /**
     * Searches for a possible config file
     *
     * @param string $baseDir path to start looking in
     *
     * @return bool false if nothing found
     */
    protected function searchConfigFile($baseDir = null)
    {
        if (is_null($baseDir) && isset($this->app_dir)) {
            $baseDir = $this->app_dir;
        } else if (is_null($baseDir) && defined('SYNERGY_ROOT_DIR')) {
            $baseDir = SYNERGY_ROOT_DIR;
        }
        if (is_string($baseDir) && is_dir($baseDir)) {
            $testfile = $baseDir . DIRECTORY_SEPARATOR . 'config';
            if ($this->isValidDirectory($testfile)) {
                $configDir = $testfile;
            } else {
                $testfile = $baseDir . DIRECTORY_SEPARATOR . 'Config';
                if ($this->isValidDirectory($testfile)) {
                    $configDir = $testfile;
                }
            }

            if (isset($configDir)) {
                $d = dir($configDir);
                while (false !== ($entry = $d->read())) {
                    if (substr($entry, 0, 1) == '.' || is_dir($entry)) {
                        continue;
                    }
                    $arr = explode('.', $entry);
                    if (strtolower($arr[0]) == 'config') {
                        try {
                            $this->setConfigFilename($configDir . DIRECTORY_SEPARATOR . $entry);
                            $d->close();
                            return true;
                        } catch (InvalidArgumentException $ex) {
                            // not a valid filename - keep trying
                        }
                    }
                }
                $d->close();
            }
        }

        return false;
    }

    /**
     * Tests the validity of the directory - can we use it?
     *
     * @param string $dirname dir to test
     *
     * @return bool
     */
    protected function isValidDirectory($dirname)
    {
        if (file_exists($dirname) && is_dir($dirname)) {
            return true;
        }

        return false;
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
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'yml':
                    $this->configFilename = $filename;
                    $this->options = \Spyc::YAMLLoad($filename);
                    break;

                default:
                    throw new InvalidArgumentException(
                        sprintf("Config file format is not supported", $filename)
                    );
            }
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


    /**
     * Value of member options
     *
     * @return array value of member
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * If a config option with the keyname exists then return the value
     * doing any variable substitution first
     *
     * @param string $keyname eg synergy:webproject:template_dir
     *
     * @return bool|mixed
     */
    public function getOption($keyname)
    {
        // iterate through our options for the specific key
        $arr = explode(':', $keyname);
        if (count($arr) > 1) {
            $testArr = $this->options;
            foreach ($arr AS $testkey)
            {
                if ($this->isArrayKeyAnArray($testArr, $testkey)) {
                    $testArr = $testArr[$testkey];
                }
            }
        } else {
            $testArr = $this->options;
        }

        if (isset($testArr[$arr[count($arr)-1]])) {
            $value = $testArr[$arr[count($arr)-1]];
            $value = $this->replaceOptionVariables($value);
            return $value;
        }

        return false;
    }


    /**
     * Replaces any variable tags (eg %app_dir%) in the value
     *
     * @param string $option_value look for variables to substitute in this string
     *
     * @return string
     */
    protected function replaceOptionVariables($option_value)
    {
        if (isset($this->app_dir)) {
            $option_value = preg_replace('/%app_dir%/', $this->app_dir, $option_value);
        }
        if (isset($this->temp_dir)) {
            $option_value = preg_replace('/%temp_dir%/', $this->temp_dir, $option_value);
        }

        return $option_value;
    }


    /**
     * Tests if the key in the array exists and returns an array
     *
     * @param $array
     * @param $key
     *
     * @return bool
     */
    protected function isArrayKeyAnArray($array, $key)
    {
        if (isset($array[$key]) && is_array($array[$key])) {
            return true;
        }
        return false;
    }


}
