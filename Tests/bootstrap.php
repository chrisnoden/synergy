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
 * @author    Chris Noden, @chrisnoden
 * @copyright 2009-2013 Chris Noden
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link      https://github.com/chrisnoden
 */

$autoloadFile = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoloadFile)) {
    $autoloadFile = __DIR__ . '/../vendor/autoload.php';
}

if (is_file($autoloadFile)) {
    $loader = include_once $autoloadFile;
    $loader->add('Synergy\Tests', __DIR__);
} else {
    throw new \LogicException('Run "composer install --dev" to create autoloader.');
}

