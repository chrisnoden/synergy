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

use Psr\Log\LoggerInterface;
use Synergy\Singleton;
use Psr\Log\LogLevel;

/**
 * Class Logger
 * A simple way to use the main Project logger
 * This is where we keep tabs on the defined logger and expose
 * easy static methods for logging
 *
 * @package Synergy\Logger
 */
class Logger extends Singleton
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private static $_logger;


    /**
     * Log using our assigned logger
     *
     * @param       $level
     * @param       $message
     * @param array $context
     */
    public static function log($level, $message, array $context = array())
    {
        if (is_null(self::$_logger)) {
            self::$_logger = new FileLogger('/tmp/synergy.log'); // @todo replace with sensible default log filename
        }

        self::$_logger->log($level, $message, $context);
    }


    /**
     * Assign the logger used for this Synergy project
     *
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$_logger = $logger;
    }


    /**
     * @return LoggerInterface
     */
    public static function getLogger()
    {
        if (is_null(self::$_logger)) {
            self::$_logger = new FileLogger('/tmp/synergy.log'); // @todo replace with sensible default log filename
        }

        return self::$_logger;
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
     * @param string $message
     * @param array  $context
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
     * @param string $message
     * @param array  $context
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
     * @param string $message
     * @param array  $context
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
     * @param string $message
     * @param array  $context
     * @return null
     */
    public static function warning($message, array $context = array())
    {
        self::log(LogLevel::WARNING, $message, $context);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
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
     * @param string $message
     * @param array  $context
     * @return null
     */
    public static function info($message, array $context = array())
    {
        self::log(LogLevel::INFO, $message, $context);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     * @return null
     */
    public static function debug($message, array $context = array())
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }
}
