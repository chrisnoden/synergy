<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Logger;


use Psr\Log\LoggerInterface;

/**
 * Provides a Psr-3 compliant File logger
 */
class FileLogger extends Logger implements LoggerInterface
{


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     * @throw \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        switch (strtolower($level))
        {
            case 'emergency':
                $this->emergency($message, $context);
                break;
            case 'alert':
                $this->alert($message, $context);
                break;
            case 'critical':
                $this->critical($message, $context);
                break;
            case 'error':
                $this->error($message, $context);
                break;
            case 'warning':
                $this->warning($message, $context);
                break;
            case 'notice':
                $this->notice($message, $context);
                break;
            case 'info':
                $this->info($message, $context);
                break;
            case 'debug':
                $this->debug($message, $context);
                break;

            default:
                throw new \Psr\Log\InvalidArgumentException('Invalid log $level, must be one of debug, info, notice, warning, error, critical, alert, emergency');
        }
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->setLevel('fatal');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->setLevel('error');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->setLevel('fatal');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->setLevel('error');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->setLevel('warn');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->setLevel('notice');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->setLevel('info');
        $this->setFieldValues($context);
        $this->write($message);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->setLevel('debug');
        $this->setFieldValues($context);
        $this->write($message);
    }
}