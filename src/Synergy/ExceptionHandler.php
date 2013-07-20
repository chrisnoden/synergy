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

namespace Synergy;

use Psr\Log\LogLevel;
use Synergy\Logger\Logger;

/**
 * Class ExceptionHandler
 *
 * handles all our debugging and logging
 * Uses the Debug static class to output any logging or error messages
 * which in turn uses Comms channels (objects) so you can send to Syslog,
 * a File, Growl and more
 *
 * @category Synergy
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class ExceptionHandler
{

    /**
     * The PHP error number
     *
     * @var int
     */
    protected static $errNum;
    /**
     * The PHP error message
     *
     * @var string
     */
    protected static $errMsg;
    /**
     * Filename that the error was raised in
     *
     * @var string
     */
    protected static $fileName;
    /**
     * The line number the error was raised in
     *
     * @var int
     */
    protected static $lineNum;
    /**
     * @var array
     */
    protected static $trace = array();
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
        0     => "info",
        1     => "error",
        2     => "warning",
        4     => "parsing error",
        8     => "notice",
        16    => "core error",
        32    => "core warning",
        64    => "compile error",
        128   => "compile warning",
        256   => "fatal error",
        512   => "big error",
        1024  => "user notice",
        2048  => "strict",
        4096  => 'recoverable error',
        8192  => 'deprecated code',
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
    private static $_aStopCodes = array(1, 4, 16, 32, 64, 128, 256, 512);
    /**
     * Array of codes and files that are ignored
     * Add to this list with a call to
     * ExceptionHandler::addIgnoreCombo($code, $file);
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
        self::$_bDevServer   = true;
        self::$_aIgnoreCodes = array();
    }


    /**
     * Add an ignore combo of error number and filename
     * If the error is thrown in the file then it will be ignored
     *
     * @param int    $errNum   PHP error number value
     * @param string $fileName the basename filename (no path)
     *
     * @static
     * @return void
     */
    public static function addIgnoreCombo($errNum, $fileName)
    {
        $aNeedle = array(
            'errNum'   => $errNum,
            'fileName' => strtolower($fileName)
        );
        if (!in_array($aNeedle, self::$_aIgnoreCombos)) {
            self::$_aIgnoreCombos[] = $aNeedle;
        }
    }


    /**
     * Can we ignore this error
     *
     * @param int    $errNum   PHP error number
     * @param string $fileName file that raised the error
     *
     * @static
     * @return bool
     */
    private static function _canIgnore($errNum, $fileName)
    {
        if (in_array($errNum, self::$_aIgnoreCodes)) {
            return true;
        }

        $aNeedle = array(
            'errNum'   => $errNum,
            'fileName' => strtolower(basename($fileName))
        );
        if (in_array($aNeedle, self::$_aIgnoreCombos)) {
            return true;
        }

        return false;
    }


    /**
     * Define an ErrorHandler
     *
     * @param int    $errNum   PHP error number
     * @param string $errMsg   error message
     * @param string $fileName file that raised the error
     * @param int    $lineNum  line that raised the error
     * @param mixed  $misc     context
     *
     * @static
     * @return bool
     */
    public static function ErrorHandler($errNum, $errMsg, $fileName, $lineNum, /** @noinspection PhpUnusedParameterInspection */
                                        $misc)
    {
        // can we safely ignore this error
        if (self::_canIgnore($errNum, $fileName)) {
            return true;
        }

        self::$errNum   = $errNum;
        self::$errMsg   = $errMsg;
        self::$fileName = $fileName;
        self::$lineNum  = $lineNum;
        self::$trace    = null;

        // process the error
        self::handler();

        // exit program execution if necessary
        if (in_array($errNum, self::$_aStopCodes)) {
            exit;
        }

        return true;
    }


    /**
     * Catch any thrown Exceptions and route them
     *
     * @param \Exception $e uncaught exception
     *
     * @static
     * @return bool
     */
    public static function ExceptionHandler(\Exception $e)
    {
        // can we safely ignore this error
        if (self::_canIgnore($e->getCode(), $e->getFile())) {
            return true;
        }

//        $ref = new \ReflectionObject($e);

        self::$errNum   = $e->getCode();
        self::$errMsg   = $e->getMessage();
        self::$fileName = $e->getFile();
        self::$lineNum  = $e->getLine();
        self::$trace    = $e->getTrace();

        // process the error
        self::handler(LogLevel::CRITICAL);

        // exit program execution if necessary
        if (in_array(self::$errNum, self::$_aStopCodes)) {
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
        if ($last_error['type'] === E_ERROR) {
            self::$errMsg   = $last_error['message'];
            self::$errNum   = $last_error['type'];
            self::$fileName = $last_error['file'];
            self::$lineNum  = $last_error['line'];
            self::$trace    = null;
            self::handler();
        }
    }


    /**
     * Sends the error to our Debug object
     *
     * @static
     */
    protected static function handler($LogLevel = null)
    {
        if (isset(self::$errNum)) {
            if (isset(self::$trace)) {
                $text = sprintf("%s\n\nTrace:\n\n", self::$errMsg);
                foreach (self::$trace AS $traceItem) {
                    foreach ($traceItem AS $key => $val) {
                        if ($key == 'file' && defined('SYNERGY_ROOT_DIR')) {
                            $val = str_ireplace(realpath(SYNERGY_ROOT_DIR) . DIRECTORY_SEPARATOR, '', $val);
                        }
                        $text .= sprintf("\t[%s] => %s\n", $key, $val);
                    }
                    $text .= "\n";
                }
            } else {
                $text = sprintf("%s", self::$errMsg);
            }

            if ($LogLevel === null) {
                /**
                 * Convert the PHP error number to a Psr compatible LogLevel
                 */
                switch (self::$errNum) {
                    case 1:
                    case 256:
                        $dbgLevel = LogLevel::CRITICAL;
                        break;

                    case 2:
                    case 512:
                        $dbgLevel = LogLevel::WARNING;
                        break;

                    case 8:
                    case 1024:
                        $dbgLevel = LogLevel::NOTICE;
                        break;

                    case 2048:
                    case 8192:
                        $dbgLevel = LogLevel::ALERT;
                        break;

                    case 16:
                        $dbgLevel = LogLevel::CRITICAL;
                        break;

                    default:
                        $dbgLevel = LogLevel::ERROR;
                }
            } else {
                $dbgLevel = $LogLevel;
            }

            // Log it through our Project Logger
            Logger::log(
                $dbgLevel,
                $text,
                array('filename' => self::$fileName, 'linenum' => self::$lineNum, 'level' => $dbgLevel)
            );
        }
    }


}

