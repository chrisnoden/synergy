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
 * @package   Synergy MVC Library
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

/**
 * Choose your own timezone if this is not appropriate
 * The possible values are here: http://uk.php.net/manual/en/timezones.php
 */
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Europe/London');
}

/**
 * Optional - Set our locale for string/character functions
 * NB This does not work on all systems so you may need to remove it
 */
setlocale(LC_ALL, 'en_GB');

// Check this is coming from the CLI
if (PHP_SAPI !== 'cli') {
    die("Must be run on command line, eg php -f ".__FILE__."\n");
}

/**
 * Stop PHP timing out
 */
set_time_limit(0);


/**
 * Synergy will base all relative files and paths on this root
 * This should be your main project directory (where your composer.json lives)
 */
define('SYNERGY_ROOT_DIR', $_SERVER['PWD']);

/**
 * Load composer autoload.php
 */
if (file_exists(
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')
) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} else if (file_exists(
    dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')
) {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} else {
    die('Error locating autoload.php'."\n");
}

/**
 * Configure PHP errors & logging
 */
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);


/**
 * Init the Synergy Exception and Error handling
 */
set_exception_handler('Synergy\ExceptionHandler::ExceptionHandler');
set_error_handler('Synergy\ExceptionHandler::ErrorHandler');

/**
 * We also register a shutdown function
 */
register_shutdown_function('Synergy\ExceptionHandler::ShutdownHandler');


