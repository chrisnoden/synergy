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

use Synergy\Singleton;
use Synergy\Logger\LogLevel;

/**
 * Class Logger
 *
 * A simple way to use the main Project logger
 * This is where we keep tabs on the defined logger and expose
 * easy static methods for logging
 *
 * @category Synergy\Logger
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Logger extends Singleton
{

    /**
     * @var LoggerInterface
     */
    private static $_logger;
    /**
     * @var bool should any console output be silenced
     */
    protected static $silentConsole = false;


    /**
     * Log using our assigned logger
     *
     * @param string $level   LogLevel
     * @param string $message Message to log
     * @param array  $context optional additional log data
     *
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        if (is_null(self::$_logger)) {
            self::setFallbackLogger();
        }

        if (!is_null(self::$_logger)) {
            self::$_logger->log($level, $message, $context);
        } else {
            // unable to log anywhere
            echo $message;
        }
    }


    /**
     * Assign the logger used for this Synergy project
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }


    /**
     * @return LoggerInterface
     */
    public static function getLogger()
    {
        if (is_null(self::$_logger)) {
            self::setFallbackLogger();
        }

        return self::$_logger;
    }


    /**
     * Create a file logger fall-back
     */
    private static function setFallbackLogger()
    {
        self::$_logger = new SynergyLogger();
        if (defined('SYNERGY_ROOT_DIR')) {
            // @todo replace with sensible default log filename
            self::$_logger->addFileLogger(
                SYNERGY_ROOT_DIR . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'project.log'
            );
        }

        if (PHP_SAPI == 'cli') {
            self::$_logger->addCliLogger();
        }
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     * @return null
     */
    public static function emergency($message, array $context = array())
    {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function alert($message, array $context = array())
    {
        self::log(LogLevel::ALERT, $message, $context);
    }


    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function critical($message, array $context = array())
    {
        self::log(LogLevel::CRITICAL, $message, $context);
    }


    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function error($message, array $context = array())
    {
        self::log(LogLevel::ERROR, $message, $context);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function warning($message, array $context = array())
    {
        self::log(LogLevel::WARNING, $message, $context);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function notice($message, array $context = array())
    {
        self::log(LogLevel::NOTICE, $message, $context);
    }


    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function info($message, array $context = array())
    {
        self::log(LogLevel::INFO, $message, $context);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message error message
     * @param array  $context any extra parameters for the error
     *
     * @return null
     */
    public static function debug($message, array $context = array())
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }


    /**
     * Set the value of silentConsole member
     *
     * @param boolean $silentConsole
     *
     * @return void
     */
    public static function setSilentConsole($silentConsole)
    {
        if ($silentConsole === true && method_exists(self::$_logger, 'setSilentConsole')) {
            self::$_logger->setSilentConsole(true);
        } else if (method_exists(self::$_logger, 'setSilentConsole')) {
            self::$_logger->setSilentConsole(false);
        }
        self::$silentConsole = $silentConsole;
    }


}
