<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * PHP version 5
 *
 * @category  Project:Synergy
 * @package   Synergy
 * @author    Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 * @link      http://chrisnoden.com
 * @license   http://opensource.org/licenses/LGPL-3.0
 */

namespace Synergy\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Synergy\Exception\InvalidArgumentException;

abstract class LoggerAbstract extends AbstractLogger
{

    /**
     * @var array
     */
    private $_aValidLogLevels = array();


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
                throw new InvalidArgumentException(
                    'logfile must be writeable by user: '.$processUser['name']
                );
            }

            $this->_filename = $filename;
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
            $logLevels = implode(
                ', \\Psr\\Log\\LogLevel::',
                $this->_aValidLogLevels
            );
            throw new \Psr\Log\InvalidArgumentException(
                'Invalid LogLevel ('.$level.', must be one of \Psr\Log\LogLevel::' . $logLevels);
        }

        return true;
    }

}