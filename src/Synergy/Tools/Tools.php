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

namespace Synergy\Tools;

/**
 * seed the random number generator
 */
list($usec, ) = explode(" ", microtime());
srand((int)($usec*10));

/**
 * Class Tools
 *
 * @category Synergy\Tools
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Tools
{

    /**
     * Generates a random string of length $length
     *
     * @param string $length how many characters do you want in your string
     * @param string $chars  seed of characters used to feed the output
     *
     * @static
     * @return string
     */
    public static function randomString(
        $length,
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
    ) {
        $size = strlen($chars);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[ rand(0, $size - 1) ];
        }

        return "$str";
    }


    /**
     * Recursively remove a directory and all contents - BE CAREFUL
     *
     * @param $dir
     *
     * @return bool
     */
    public static function removeDir($dir)
    {
        if (strlen($dir) > 4) {
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? self::removeDir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
    }


    /**
     * Creates a folder if it doesn't exist (plus the parent folders)
     * Optionally tests it (even if it already exists) for
     * read & write permissions by the platform
     *
     * @param string $path folder we wish tested/created
     * @param bool   $test default=true test the folder for write permissions
     *
     * @static
     * @return bool true if created/exists and is read/writeable
     */
    public static function mkdir($path, $test = true)
    {
        if (!file_exists($path) || !is_dir($path)) {
            @mkdir($path, 0770, true);
        }
        // Test the folder for suitability
        if (file_exists($path) && is_readable($path) && is_dir($path)) {
            if ($test) {
                // Try to save something in the path
                @touch($path . DIRECTORY_SEPARATOR . 'testfile');
                if (file_exists($path . DIRECTORY_SEPARATOR . 'testfile')) {
                    unlink($path . DIRECTORY_SEPARATOR . 'testfile');
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }


    /**
     * A Signal safe equivalent to sleep
     * If a POSIX signal is received while pausing then the pause is resumed
     *
     * @param int $seconds
     */
    public static function pause($seconds = 1)
    {
        if (!is_int($seconds)) {
            return;
        }
        $microseconds = intval($seconds*1000000);
        $start = microtime(true);
        $diff = $microseconds;
        do {
            usleep($diff);
            $actual = intval((microtime(true) - $start)*1000000);
            $diff = $microseconds - $actual;
        } while ($diff > 10000);
    }
}
