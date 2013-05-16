<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy;

use Psr\Log\LogLevel;
use Synergy\Logger\Logger;


/**
 * handles all our debugging and logging
 * Uses the Debug static class to output any logging or error messages
 * which in turn uses Comms channels (objects) so you can send to Syslog, a File, Growl and more
 */
class ExceptionHandler
{
    /**
     * The PHP error number
     *
     * @var int
     */
    protected static $_errNum;
    /**
     * The PHP error message
     *
     * @var string
     */
    protected static $_errMsg;
    /**
     * Filename that the error was raised in
     *
     * @var string
     */
    protected static $_fileName;
    /**
     * The line number the error was raised in
     *
     * @var int
     */
    protected static $_lineNum;
    /**
     * @var array
     */
    protected static $_trace = array();
    /**
     * @var bool
     */
    private static $_bDevServer = false;
    /**
     * Array of possible error numbers
     * with a more human readable string value
     *
     * @var array
     */
    private static $_aErrorTypes = array(
        0 => "info",
        1 => "error",
        2 => "warning",
        4 => "parsing error",
        8 => "notice",
        16 => "core error",
        32 => "core warning",
        64 => "compile error",
        128 => "compile warning",
        256 => "fatal error",
        512 => "big error",
        1024 => "user notice",
        2048 => "strict",
        4096 => 'recoverable error',
        8192 => 'deprecated code',
        16384 => 'platform deprecated code'
    );
    /**
     * Array of error codes we choose to completely ignore
     *
     * @var array
     */
    private static $_aIgnoreCodes = array(8);
    /**
     * Array of error codes that we must terminate program execution for
     *
     * @var array
     */
    private static $_aStopCodes = array(1, 2, 4, 16, 32, 64, 128, 256, 512);
    /**
     * Array of codes and files that are ignored
     * Add to this list with a call to SAL_ExceptionHandler::addIgnoreCombo($code, $file);
     *
     * @var array
     */
    private static $_aIgnoreCombos = array();



    /**
     * Set this instance as a dev server (more logging)
     *
     * @static
     * @return void
     */
    public static function setDevServer()
    {
        self::$_bDevServer = true;
        self::$_aIgnoreCodes = array();
    }


    /**
     * Add an ignore combo of error number and filename
     * If the error is thrown in the file then it will be ignored
     *
     * @static
     * @param $errNum int PHP error number value
     * @param $fileName string the basename filename (no path)
     * @return void
     */
    public static function addIgnoreCombo($errNum, $fileName)
    {
        $aNeedle = array(
            'errNum' => $errNum,
            'fileName' => strtolower($fileName)
        );
        if (!in_array($aNeedle, self::$_aIgnoreCombos)) {
            self::$_aIgnoreCombos[] = $aNeedle;
        }
    }


    /**
     * @static
     * @param $errNum
     * @param $fileName
     * @return bool
     */
    private static function canIgnore($errNum, $fileName)
    {
        if ( in_array($errNum, self::$_aIgnoreCodes) ) {
            return true;
        }

        $aNeedle = array(
            'errNum' => $errNum,
            'fileName' => strtolower(basename($fileName))
        );
        if ( in_array($aNeedle, self::$_aIgnoreCombos) ) {
            return true;
        }
    }


    /**
     * @param $errNum int
     * @param $errMsg string
     * @param $fileName string
     * @param $lineNum int
     * @param $misc mixed
     * @static
     * @return bool
     */
    public static function ErrorHandler($errNum, $errMsg, $fileName, $lineNum, $misc) {
        // can we safely ignore this error
        if ( self::canIgnore($errNum, $fileName) ) {
            return true;
        }

        self::$_errNum = $errNum;
        self::$_errMsg = $errMsg;
        self::$_fileName = $fileName;
        self::$_lineNum = $lineNum;
        self::$_trace = null;

        // process the error
        self::handler();

        // exit program execution if necessary
        if ( in_array($errNum, self::$_aStopCodes) ) {
            exit;
        }

        return true;
    }


    /**
     * Catch any thrown Exceptions and route them
     *
     * @static
     * @param \Exception $e
     * @return bool
     */
    public static function ExceptionHandler(\Exception $e) {
        // can we safely ignore this error
        if ( self::canIgnore($e->getCode(), $e->getFile()) ) {
            return true;
        }

        $ref = new \ReflectionObject($e);

        self::$_errNum = $e->getCode();
        self::$_errMsg = $e->getMessage();
        self::$_fileName = $e->getFile();
        self::$_lineNum = $e->getLine();
        self::$_trace = $e->getTrace();

        // process the error
        self::handler(LogLevel::CRITICAL);

        // exit program execution if necessary
        if ( in_array(self::$_errNum, self::$_aStopCodes) ) {
            exit;
        }

        return true;
    }


    /**
     * When the script ends this runs as part of the garbage collection
     * so we can output any final errors
     *
     * @static
     */
    public static function ShutdownHandler()
    {
        $last_error = error_get_last();
        if ( $last_error['type'] === E_ERROR ) {
            self::$_errMsg = $last_error['message'];
            self::$_errNum = $last_error['type'];
            self::$_fileName = $last_error['file'];
            self::$_lineNum = $last_error['line'];
            self::$_trace = null;
            self::handler();
        }
    }


    /**
     * Sends the error to our Debug object
     *
     * @static
     */
    protected static function handler($type = null)
    {
        if (isset(self::$_errNum)) {
            if (isset(self::$_trace)) {
                $text = sprintf("%s\n\nTrace:\n\n", self::$_errMsg);
                foreach (self::$_trace AS $traceItem)
                {
                    foreach ($traceItem AS $key=>$val)
                    {
                        $text .= sprintf("\t[%s] => %s\n", $key, $val);
                    }
                    $text .= "\n";
                }
            } else {
                $text = sprintf("%s", self::$_errMsg);
            }

            if ($type === null) {
                /**
                 * Convert the PHP error number to a Psr compatible LogLevel
                 */
                switch (self::$_errNum)
                {
                    case 2:
                        $dbgLevel = LogLevel::INFO;
                        break;

                    case 8:
                    case 1024:
                        $dbgLevel = LogLevel::NOTICE;
                        break;

                    case 2048:
                        $dbgLevel = LogLevel::WARNING;
                        break;

                    case 16:
                        $dbgLevel = LogLevel::CRITICAL;
                        break;

                    default:
                        $dbgLevel = LogLevel::ERROR;
                }
            } else {
                $dbgLevel = $type;
            }

            // Log it through our Project Logger
            Logger::log(
                $dbgLevel,
                $text,
                array('filename'=>self::$_fileName, 'linenum'=>self::$_lineNum, 'level'=>self::$_aErrorTypes[self::$_errNum])
            );
        }
    }



}

