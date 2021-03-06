<?php
/**
 * Created by Chris Noden using PhpStorm.
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
 * @package   synergy
 * @author    Chris Noden <chris.noden@gmail.com>
 * @copyright 2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php')) {
    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php');

    $sh = new \Synergy\Project\Web\SessionHandler\FileSessionHandler();
    @session_start();
    $_SESSION['name'] = __FILE__;
    $_SESSION['time'] = time();

    printf('%s', serialize($_SESSION));

    session_destroy();
}
