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
 *
 * Holds central config data used by your ProjectAbstract child object
 *
 * @category Synergy
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
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
    private static $projectName;
    /**
     * @var bool is this a Dev project
     */
    private static $isDev = false;
    /**
     * @var \Synergy\Project\ProjectAbstract
     */
    private static $projectInstance;
    /**
     * @var string
     */
    private static $projectType;
    /**
     * @var array
     */
    private static $options = array();


    /**
     * Initialise the static Project data
     *
     * @return void
     */
    public static function init()
    {
        if (!defined('SYNERGY_LIBRARY_PATH')) {
            define('SYNERGY_LIBRARY_PATH', dirname(__FILE__));
        }

        self::$projectName = null;
        self::$projectInstance = null;
        self::$projectType = null;
        self::$options = array();
    }


    /**
     * Set the Project Object
     *
     * @param ProjectAbstract $object Project Object
     *
     * @return void
     */
    public static function setObject(ProjectAbstract $object)
    {
        self::$projectInstance = $object;
    }


    /**
     * The Project Object
     *
     * @return ProjectAbstract Project object
     */
    public static function getObject()
    {
        if (!self::$projectInstance instanceof ProjectAbstract) {
            if (isset(self::$projectType)) {
                $classname = "Synergy\\Project\\" .
                    ucfirst(self::$projectType) .
                    "\\" .
                    ucfirst(self::$projectType) .
                    'Project'; // eg Synergy\Project\Web\WebProject
                self::$projectInstance = new $classname();
            }
        }

        return self::$projectInstance;
    }


    /**
     * An alias for Synergy\Logger\Logger::setLogger()
     *
     * @param \Psr\Log\LoggerInterface $logger object must implement the Psr-3 standard
     *
     * @return void
     */
    public static function setLogger(\Psr\Log\LoggerInterface $logger)
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
     * @param string $projectName nice name of our project (30 char limit)
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public static function setName($projectName)
    {
        if (is_string($projectName) && mb_strlen(trim($projectName), 'utf-8') < 30) {
            self::$projectName = trim($projectName);
        } else {
            throw new InvalidArgumentException(
                "projectName must a string, max 30 chars"
            );
        }
    }


    /**
     * Name of the project (30 char limit)
     *
     * @return string name of our Project
     */
    public static function getName()
    {
        return self::$projectName;
    }


    /**
     * Type of project
     *
     * @param string $projectType one of the Project\ProjectType class constants
     *
     * @throws InvalidProjectTypeException
     * @return void
     */
    public static function setType($projectType)
    {
        self::$projectType = $projectType;
    }


    /**
     * Project\ProjectType class constant
     *
     * @return string project type
     */
    public static function getType()
    {
        return self::$projectType;
    }


    /**
     * Set true if this is a development environment and we want more verbose logging
     *
     * @param bool $isDev dev environment
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public static function setDev($isDev)
    {
        if (is_bool($isDev)) {
            self::$isDev = $isDev;
        } else if (is_int($isDev)) {
            self::$isDev = $isDev === 0 ? false : true;
        } else if (is_string($isDev) && strlen($isDev) == 1) {
            self::$isDev = strtolower($isDev) == 'y' ? true : false;
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
        return self::$isDev;
    }


    /**
     * Is this a development environment
     *
     * @return bool
     */
    public static function getDev()
    {
        return self::$isDev;
    }


    /**
     * Set optional extra key:value pairs for the Project
     *
     * @param array $options extra params to pass to the Project
     *
     * @return void
     */
    public static function setOptions(array $options)
    {
        if (is_array($options)) {
            self::$options = $options;
        }
    }


    /**
     * @return array
     */
    public static function getOptions()
    {
        return self::$options;
    }

}

