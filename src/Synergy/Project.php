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

namespace Synergy;

use Synergy\Logger\LoggerInterface;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\InvalidProjectTypeException;
use Synergy\Logger\Logger;
use Synergy\Project\ProjectAbstract;
use Synergy\Project\ProjectType;

/**
 * Class Project
 * Holds central config data used by your ProjectAbstract child object
 *
 * @package Synergy
 */
final class Project extends Singleton
{

    /**
     * @var Singleton
     */
    protected static $instance;
    /**
     * @var string Name of our project
     */
    private static $_projectName;
    /**
     * @var bool is this a Dev project
     */
    private static $_isDev = false;
    /**
     * @var string path to the root of the project
     */
    private static $_projectPath;
    /**
     * @var string filename of the project config xml file
     */
    private static $_configFile;
    /**
     * @var \Synergy\Project\ProjectAbstract
     */
    private static $_projectInstance;
    /**
     * @var string
     */
    private static $_projectType;
    /**
     * @var array
     */
    private static $_options = array();


    public static function init()
    {
        if (!defined('SYNERGY_LIBRARY_PATH')) {
            define('SYNERGY_LIBRARY_PATH', dirname(__FILE__));
        }
        if (!defined('SYNERGY_WEB_ROOT')) {
            Project::getLogger()->error('Should define SYNERGY_WEB_ROOT with your web host path');
            define('SYNERGY_WEB_ROOT', dirname(dirname(dirname(__FILE__))));
        }

        self::$_projectName = null;
        self::$_projectInstance = null;
        self::$_projectType = null;
        self::$_options = array();
    }


    /**
     * @param ProjectAbstract $object
     */
    public static function setObject(ProjectAbstract $object)
    {
        self::$_projectInstance = $object;
    }


    /**
     * @return ProjectAbstract
     */
    public static function getObject()
    {
        if (!self::$_projectInstance instanceof ProjectAbstract) {
            if (isset(self::$_projectType)) {
                $classname = "Synergy\\Project\\" . ucfirst(self::$_projectType) . "\\" . ucfirst(self::$_projectType) . 'Project'; // eg Synergy\Project\Web\WebProject
                self::$_projectInstance = new $classname();
            }
        }

        return self::$_projectInstance;
    }


    /**
     * An alias for Synergy\Logger\Logger::setLogger()
     *
     * @param LoggerInterface $logger object must implement the Psr-3 standard
     */
    public static function setLogger(LoggerInterface $logger)
    {
        Logger::setLogger($logger);
    }


    /**
     * An alias for Synergy\Logger\Logger::getLogger()
     *
     * @return LoggerInterface
     */
    public static function getLogger()
    {
        return Logger::getLogger();
    }


    /**
     * Set the project name
     *
     * @param $projectName
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setName($projectName)
    {
        if (is_string($projectName) && mb_strlen(trim($projectName), 'utf-8') < 30) {
            self::$_projectName = trim($projectName);
        } else {
            throw new InvalidArgumentException("projectName must a string, max 30 chars");
        }
    }


    /**
     * @return string name of our Project
     */
    public static function getName()
    {
        return self::$_projectName;
    }


    /**
     * @param $projectType string one of the Project\ProjectType class constants
     * @throws \Synergy\Exception\InvalidProjectTypeException
     */
    public static function setType($projectType)
    {
        $t = ProjectType::getInstance();
        $r = new \ReflectionObject($t);
        $aConstants = $r->getConstants();

        if (!in_array($projectType, $aConstants)) {
            throw new InvalidProjectTypeException('projectType must be one of ' . implode(', ', $aConstants));
        }

        self::$_projectType = $projectType;
    }


    /**
     * @return string
     */
    public static function getType()
    {
        return self::$_projectType;
    }


    /**
     * Set true if this is a development environment and we want more verbose logging
     *
     * @param $isDev
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setDev($isDev)
    {
        if (is_bool($isDev)) {
            self::$_isDev = $isDev;
        } else if (is_int($isDev)) {
            self::$_isDev = $isDev === 0 ? false : true;
        } else if (is_string($isDev) && strlen($isDev) == 1) {
            self::$_isDev = strtolower($isDev) == 'y' ? true : false;
        } else {
            throw new InvalidArgumentException("setDev expects a boolean argument");
        }
    }


    /**
     * Is this a development environment
     *
     * @return bool
     */
    public static function isDev()
    {
        return self::$_isDev;
    }


    /**
     * Is this a development environment
     *
     * @return bool
     */
    public static function getDev()
    {
        return self::$_isDev;
    }


    /**
     * @param $path string project directory (no trailing slash)
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setProjectPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                "projectPath must be a string to the directory path"
            );
        } else if (!is_dir($path)) {
            throw new InvalidArgumentException(
                "projectPath must be the path to your project directory"
            );
        } else if (!is_readable($path)) {
            throw new InvalidArgumentException(
                "projectPath must have read permissions by user:" .
                get_current_user()
            );
        }

        self::$_projectPath = $path;
    }


    /**
     * @return string path of our Project code
     */
    public static function getProjectPath()
    {
        return self::$_projectPath;
    }


    /**
     * Set the absolute filename of your project config xml
     *
     * @param string $filename location of our config file
     *
     * @return void
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setProjectConfigFilename($filename)
    {
        // check the filename is valid before setting
        if (is_string($filename)
            && substr($filename, 0, 1) == DIRECTORY_SEPARATOR
            && is_dir(dirname($filename))
            && file_exists($filename)
            && is_readable($filename)
        ) {
            self::$_configFile = $filename;
        } else if (is_string($filename) && isset(self::$_projectPath)) {
            $testFilename = self::$_projectPath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir(dirname($testFilename))
                && file_exists($testFilename)
                && is_readable($testFilename)
            ) {
                self::$_configFile = $testFilename;
            }
        }
        if (!isset(self::$_configFile)) {
            throw new InvalidArgumentException("filename must be an absolute filename to your config XML");
        }
    }


    /**
     * @return string
     */
    public static function getProjectConfigFilename()
    {
        if (isset(self::$_configFile)) {
            return self::$_configFile;
        }

        return null;
    }


    /**
     * @param array $options
     */
    public static function setOptions(array $options)
    {
        if (is_array($options)) {
            self::$_options = $options;
        }
    }


    /**
     * @return array
     */
    public static function getOptions()
    {
        return self::$_options;
    }

}

