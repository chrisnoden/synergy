<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Synergy\Exception\InvalidArgumentException;

/**
 * Provides a Psr-3 compliant File logger
 * This is a simple logger for Synergy - ideally you would use
 * a more advanced logger (like apache/log4php or chrisnoden/talkback)
 * and attach to your Project using \Synergy\Project::setLogger($logger);
 */
class FileLogger extends AbstractLogger implements LoggerInterface
{

    /**
     * @var array
     */
    protected $_aValidLogLevels = array();
    /**
     * @var string
     */
    protected $_filename;
    /**
     * @var resource
     */
    protected $_fh;


    /**
     * @param null $filename optional filename (path + filename)
     */
    public function __construct($filename = null)
    {
        /**
         * Populate our valid log levels by Reflecting on the
         * constants exposed in the Psr\Log\LogLevel class
         */
        $t = new LogLevel();
        $r = new \ReflectionObject($t);
        $this->_aValidLogLevels = $r->getConstants();

        // Set our filename
        if (!is_null($filename)) {
            if (file_exists($filename) && !is_writable($filename)) {
                $processUser = posix_getpwuid(posix_geteuid());
                throw new InvalidArgumentException('logfile must be writeable by user: '.$processUser['name']);
            }

            $this->_filename = $filename;
        }
    }


    /**
     * Logs to the File
     * @todo do something sensible with the log context
     * @todo filter the logs by level
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     * @throw \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
        if ($this->isValidLogLevel($level)) {
            $this->write($message);
        }
    }


    /**
     * Tests the $level to ensure it's accepted under the Psr3 standard
     *
     * @param $level
     * @return bool
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function isValidLogLevel($level)
    {
        if (!in_array($level, $this->_aValidLogLevels)) {
            $logLevels = implode(', \\Psr\\Log\\LogLevel::', $this->_aValidLogLevels);
            throw new \Psr\Log\InvalidArgumentException('Invalid LogLevel ('.$level.', must be one of \Psr\Log\LogLevel::' . $logLevels);
        }

        return true;
    }


    /**
     * Opens the file handler for append
     *
     * @throws InvalidArgumentException
     */
    protected function openFH()
    {
        if (!is_resource($this->_fh)) {
            if (isset($this->_filename)) {
                $fh = @fopen($this->_filename, 'a');
                if (is_resource($fh)) {
                    $this->_fh = $fh;
                } else {
                    throw new InvalidArgumentException(sprintf("Invalid filename, unable to open for append (%s)", $this->_filename));
                }
            } else {
                throw new InvalidArgumentException(sprintf("Invalid filename: %s", $this->_filename));
            }
        }
    }


    /**
     * Closes the file handler
     */
    protected function closeFH()
    {
        if (is_resource($this->_fh)) {
            @fclose($this->_fh);
            $this->_fh = null;
        }
    }


    /**
     * @param $filename
     *
     * @throws InvalidArgumentException
     */
    public function setFilename($filename)
    {
        // close any open file resource before changing the filename
        $this->closeFH();

        // check the filename is valid before setting
        if (is_string($filename) && substr($filename, 0, 1) == DIRECTORY_SEPARATOR && is_dir(dirname($filename)) && is_writable(dirname($filename))) {
            $filename = trim($filename);

            // split out the parts of the filename
            $parts = pathinfo($filename);

            // clean the filename
            $filename = $parts['dirname'] . DIRECTORY_SEPARATOR . preg_replace("/[^A-Za-z0-9+]/", '_', $parts['filename']);
            if (isset($parts['extension']) && strlen($parts['extension']) > 0) {
                $filename .= '.' . $parts['extension'];
            }

            $this->_filename = $filename;
        } else {
            throw new InvalidArgumentException("filename must be an absolute filename in a writeable directory");
        }
    }


    /**
     * @param $msg string string to add to the file
     */
    public function write($msg)
    {
        $this->openFH();
        $msg .= "\n";
        fputs($this->_fh, $msg, strlen($msg));
    }

}