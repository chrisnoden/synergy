<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */


namespace Synergy\Project;


use Psr\Log\LogLevel;
use Synergy\Exception\ProjectException;
use Synergy\Exception\SynergyException;
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
    protected $_timeStart;
    /**
     * @var string the path to the SAL platform directory
     */
    protected $_platformPath;
    /**
     * @var @string path where our working temp folder (read-writable) exists
     */
    protected $_tempFolderPath;



    /**
     * Important to execute this in any child classes
     * preferably BEFORE running any of your own code
     */
    public function __construct()
    {
        $this->_timeStart = microtime(true);

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
        $synergy_execTime = number_format($synergy_endTime - $this->_timeStart, 4);
        if (Project::isDev()) {
            Project::getLogger()->log(LogLevel::INFO, "Execution time=$synergy_execTime seconds");
        }
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function debugger()
    {
        return Project::getLogger();
    }


    /**
     * @throws \Synergy\Exception\ProjectException
     */
    public function prepare()
    {
        if (!Project::getProjectConfigFilename()) {
            throw new ProjectException("Config XML filename not set");
        }
        if (!Project::getProjectPath()) {
            Project::setProjectPath(dirname(SAL_PLATFORM_DIRECTORY));
            Project::getLogger()->log('Guessing projectPath of '.Project::getProjectPath(), Project::getLogger()->NOTICE);
        }

        /**
         * Set our Project autoloader
         */
        spl_autoload_register(array($this, 'autoload'));
    }


    /**
     * @param $path
     * @param bool $throwExceptions
     * @return bool|string false if path not valid|absolute path if the dir is valid
     * @throws Exception
     */
    protected function checkPlatformDir($path, $throwExceptions = false)
    {
        if (!file_exists($path) && substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
            $path = SAL_PLATFORM_DIRECTORY . DIRECTORY_SEPARATOR . $path;
        }

        if ($this->checkDir($path, $throwExceptions)) {
            return $path;
        }
    }


    /**
     * @param $path
     * @param $throwExceptions
     * @return bool
     * @throws SalException
     */
    protected function checkDir($path, $throwExceptions)
    {
        if (file_exists($path) && is_dir($path) && is_readable($path)) {
            return true;
        } else if (!file_exists($path)) {
            if ($throwExceptions) {
                throw new SalException('Path ('.$path.') must be an absolute path and must exist');
            } else {
                return false;
            }
        } else if (!is_dir($path)) {
            if ($throwExceptions) {
                throw new SalException($path . ' must be a directory');
            } else {
                return false;
            }
        } else if (!is_readable($path)) {
            if ($throwExceptions) {
                throw new SalException($path . ' is not readable by user: ' . get_current_user());
            } else {
                return false;
            }
        }
    }


    /**
     * @param $path
     * @param bool $throwExceptions
     * @return bool|string false if path not valid|absolute path if the dir is valid
     * @throws SalException
     */
    protected function checkProjectDir($path, $throwExceptions = false)
    {
        if (!file_exists($path) && substr($path, 0, 1) != DIRECTORY_SEPARATOR && Project::getProjectPath()) {
            $path = Project::getProjectPath() . DIRECTORY_SEPARATOR . $path;
        }

        if ($this->checkDir($path, $throwExceptions)) {
            return $path;
        }
    }


    /**
     * create/test our preferred temp folder structure
     *
     * @param $path string path where we wish to store any project temp files/caches
     */
    protected function initTempFolder($path)
    {
        $documentRoot = $this->initProjectDir($path);
        if ($documentRoot) {
            $this->_tempFolderPath = $documentRoot;
            return;
        }
        // The preferred temp folder location failed - try a fallback
        $documentRoot = $this->initProjectDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . escapeshellarg(Project::getProjectName()));
        if ($documentRoot) {
            $this->_tempFolderPath = $documentRoot;
            return;
        }
    }

    /**
     * Creates and tests a folder that is required by the project
     *
     * @param $path
     * @return $path full path
     * @throws Exception
     */
    protected function initProjectDir($path, $throwExceptions = true)
    {
        // Is this path relative to our project path
        if (!file_exists($path) && substr($path, 0, 1) != DIRECTORY_SEPARATOR && Project::getProjectPath()) {
            $path = Project::getProjectPath() . DIRECTORY_SEPARATOR . $path;
        }

        if (!$this->mkdir($path)) {
            if ($throwExceptions) {
                if (!file_exists($path)) throw new SalException('Project Path must be an absolute path and must exist');
                if (!is_writable($path)) throw new SalException($path . ' folder is not writeable');
                if (!is_dir($path)) throw new SalException($path . ' must be a directory');
                if (!is_readable($path)) throw new SalException($path . ' is not readable by user: ' . get_current_user());
            }
            return false;
        }
        return $path;
    }


    /**
     * Creates a folder if it doesn't exist (plus the parent folders)
     * Optionally tests it (even if it already exists) for read & write permissions by the platform
     *
     * @param $path string folder we wish tested/created
     * @param $test bool default=true test the folder for write permissions
     * @return bool true if created/exists and is read/writeable
     */
    protected function mkdir($path, $test=true)
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


    /**
     * Autoloader for Project classes
     *
     * @param $className
     */
    protected function autoload($className)
    {
        /**
         * @var $xml \SimpleXMLElement
         */
        $xml = Config::getBlock('classes');
        if (is_a($xml, 'SimpleXMLElement')) {
            /**
             * Are we in a namespace
             */
            $aParts = explode('\\', $className);

            // Look for the full namespace in the class name
            $aFound = $xml->xpath("//class[@name='".$className."']");

            if (count($aFound) == 0 && count($aParts) == 2) {
                // We can deal with single namespace definitions for now
                $namespace = $aParts[0];
                $className = $aParts[1];

                // Search for a block of xml in the <$namespace> zone
                $aFound = $xml->$namespace->xpath("//class[@name='".$className."']");
            }

            if (count($aFound) > 0) {
                foreach ($aFound AS $element)
                {
                    if (is_a($element, 'SimpleXMLElement')) {
                        $path = $this->checkProjectDir($element->path, true);
                        if ($path) {
                            $testFile = $path . DIRECTORY_SEPARATOR . $element->file;
                            if (file_exists($testFile) && is_readable($testFile)) {
                                require_once $testFile;
                                break;
                            }
                        }
                    }
                }

            }
        }
        $xml = null;
        unset($xml);
    }


}
