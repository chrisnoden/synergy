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

namespace Synergy\Logger;

use Synergy\Logger\LoggerAbstract;
use Synergy\Logger\LoggerInterface;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Tools\Tools;

/**
 * Class FileLogger
 *
 * Provides a Psr-3 compliant File logger
 * This is a simple logger for Synergy - ideally you would use
 * a more advanced logger (like apache/log4php or chrisnoden/talkback)
 * and attach to your Project using \Synergy\Project::setLogger($logger);
 *
 * @category Synergy\Logger
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class FileLogger extends LoggerAbstract implements LoggerInterface
{

    /**
     * @var string
     */
    protected $filename;
    /**
     * @var resource
     */
    protected $fh;


    /**
     * Create a new FileLogger object
     *
     * @param null $filename optional filename (path + filename)
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public function __construct($filename = null)
    {
        parent::__construct();
        if (!is_null($filename)) {
            $this->setFilename($filename);
        }
    }


    /**
     * Logs to the File
     *
     * @todo do something sensible with the log context
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     * @throw InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
        if ($this->isValidLogLevel($level)) {
            switch ($level) {
                case LogLevel::EMERGENCY:
                case LogLevel::CRITICAL:
                case LogLevel::ERROR:
                case LogLevel::ALERT:
                    $this->_write(
                        sprintf('%s %s %s',
                            date('Y-m-d H:i:s'),
                            $level,
                            $message
                        )
                    );
                    break;
            }

        }
    }


    /**
     * Opens the file handler for append
     *
     * @throws InvalidArgumentException
     */
    protected function openFH()
    {
        if (!is_resource($this->fh)) {
            if (isset($this->filename)) {
                $fh = @fopen($this->filename, 'a');
                if (is_resource($fh)) {
                    $this->fh = $fh;
                } else {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Invalid filename, unable to open for append (%s)",
                            $this->filename
                        )
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    sprintf("Invalid filename: %s", $this->filename)
                );
            }
        }
    }


    /**
     * Closes the file handler
     */
    protected function closeFH()
    {
        if (is_resource($this->fh)) {
            @fclose($this->fh);
            $this->fh = null;
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

        $filename = trim($filename);

        // check the filename is valid before setting
        if (is_string($filename) && substr($filename, 0, 1) == DIRECTORY_SEPARATOR) {
            // split out the parts of the filename
            $parts = pathinfo($filename);

            // clean the filename
            $filename = $parts['dirname'] . DIRECTORY_SEPARATOR . preg_replace("/[^A-Za-z0-9+]/", '_', $parts['filename']);
            if (isset($parts['extension']) && strlen($parts['extension']) > 0) {
                $filename .= '.' . $parts['extension'];
            }

            // test the dir
            if (!is_dir($parts['dirname'])) {
                if (!Tools::mkdir($parts['dirname'], true)) {
                    throw new InvalidArgumentException(
                        "filename must be an absolute filename in a writeable directory : $filename"
                    );
                }
            }

            // Test an existing file is writable
            if (file_exists($filename) && !is_writable($filename)) {
                $processUser = posix_getpwuid(posix_geteuid());
                throw new InvalidArgumentException(
                    'logfile must be writeable by user: '.$processUser['name']
                );
            } else if (!file_exists($filename) && is_dir($parts['dirname']) && is_writable($parts['dirname'])) {
                touch($filename);
            }

            $this->filename = $filename;
        } else {
            throw new InvalidArgumentException(
                "filename must be an absolute filename in a writeable directory : $filename"
            );
        }
    }


    /**
     * @param $msg string string to add to the file
     */
    private function _write($msg)
    {
        $this->openFH();
        $msg .= "\n";
        fputs($this->fh, $msg, strlen($msg));
    }

}