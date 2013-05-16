<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */


namespace Synergy;


use Psr\Log\LoggerInterface;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\InvalidProjectTypeException;
use Synergy\Logger\FileLogger;
use Synergy\Logger\Logger;
use Synergy\Project\WebProject;

class Project extends Singleton
{
    const WEB = 'Web';
    const CLI = 'CLI';
    const DAEMON = 'Daemon';

    /**
     * @var Singleton
     */
    protected static $instance;
    /**
     * @var string Name of our project
     */
    private static $_projectName = 'Synergy';
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
     * @var \Synergy\Logger\Logger
     */
    private static $_logger;


    /**
     * Launch a new Project
     *
     * @param $projectType
     * @throws \Synergy\Exception\InvalidProjectTypeException
     */
    public static function launch($projectType)
    {
        switch ($projectType)
        {
            case self::WEB:
                self::$_projectInstance = new WebProject();
                break;

            case self::CLI:
            case self::DAEMON:
                // good project type
                break;

            default:
                throw new InvalidProjectTypeException('Invalid project type, should be one of Synergy\Project::WEB ::CLI or ::DAEMON');
        }

        // the new way
        self::$_projectInstance->launch();
    }


    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    public static function Logger()
    {
        if (!self::$_logger instanceof LoggerInterface) {
            self::$_logger = new FileLogger('/tmp/synergy.log'); // @todo replace with sensible default log filename
        }

        return self::$_logger;
    }


    /**
     * Set the project name
     *
     * @param $projectName
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setProjectName($projectName)
    {
        if (is_string($projectName) && mb_strlen(trim($projectName), 'utf-8') < 30) {
            self::$_projectName = trim($projectName);
        } else {
            throw new InvalidArgumentException("projectName must a string, max 30 chars");
        }
    }


    /**
     * @return $projectName Name of our project (30 char limit)
     */
    public static function getProjectName()
    {
        if (isset(self::$_projectName)) {
            return self::$_projectName;
        }
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
            throw new InvalidArgumentException("projectPath must be a string to the directory path");
        } else if (!is_dir($path)) {
            throw new InvalidArgumentException("projectPath must be the path to your project directory");
        } else if (!is_readable($path)) {
            throw new InvalidArgumentException("projectPath must have read permissions by user:".get_current_user());
        }

        self::$_projectPath = $path;
    }


    /**
     * @return string
     */
    public static function getProjectPath()
    {
        if (isset(self::$_projectPath)) {
            return self::$_projectPath;
        }
    }


    /**
     * Set the absolute filename of your project config xml
     * @param $filename
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public static function setProjectConfigFilename($filename)
    {
        // check the filename is valid before setting
        if (is_string($filename) && substr($filename, 0, 1) == DIRECTORY_SEPARATOR && is_dir(dirname($filename)) && file_exists($filename) && is_readable($filename)) {
            self::$_configFile = $filename;
        } else if (is_string($filename) && isset(self::$_projectPath)) {
            $testFilename = self::$_projectPath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir(dirname($testFilename)) && file_exists($testFilename) && is_readable($testFilename)) {
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
    }

}

