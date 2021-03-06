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

use Psr\Log\AbstractLogger;
use Synergy\Exception\InvalidArgumentException;

/**
 * Class LoggerAbstract
 *
 * @category Synergy\Logger
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
abstract class LoggerAbstract extends AbstractLogger
{

    /**
     * @var array
     */
    protected $aValidLogLevels = array();


    public function __construct()
    {
        /**
         * Populate our valid log levels by Reflecting on the
         * constants exposed in the Psr\Log\LogLevel class
         */
        $t = new LogLevel();
        $r = new \ReflectionObject($t);
        $this->aValidLogLevels = $r->getConstants();
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
        if (!in_array($level, $this->aValidLogLevels)) {
            $logLevels = implode(
                ', \\Psr\\Log\\LogLevel::',
                $this->aValidLogLevels
            );
            throw new InvalidArgumentException(
                'Invalid LogLevel ('.$level.', must be one of \Psr\Log\LogLevel::' . $logLevels);
        }

        return true;
    }

}