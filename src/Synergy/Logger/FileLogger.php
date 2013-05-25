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

/**
 * Provides a Psr-3 compliant File logger
 * This is a simple logger for Synergy - ideally you would use
 * a more advanced logger (like apache/log4php or chrisnoden/talkback)
 * and attach to your Project using \Synergy\Project::setLogger($logger);
 */
class FileLogger extends LoggerAbstract implements LoggerInterface
{

    /**
     * @var string
     */
    protected $_filename;
    /**
     * @var resource
     */
    protected $_fh;


    /**
     * Logs to the File
     *
     * @todo do something sensible with the log context
     * @todo filter the logs by level
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
            $this->_write($message);
        }
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
                    throw new InvalidArgumentException(
                        sprintf("Invalid filename, unable to open for append (%s)", $this->_filename)
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    sprintf("Invalid filename: %s", $this->_filename)
                );
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
    private function _write($msg)
    {
        $this->openFH();
        $msg .= "\n";
        fputs($this->_fh, $msg, strlen($msg));
    }

}