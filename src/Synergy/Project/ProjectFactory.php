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

namespace Synergy\Project;

use Synergy\Project;

/**
 * Class ProjectFactory
 * Instantiate and return the relevant ProjectType
 *
 * @package Synergy\Project
 */
final class ProjectFactory
{

    /**
     * Create a new Synergy Project
     *
     * @param                          $projectName
     * @param                          $projectType
     * @param \Synergy\Logger\LoggerInterface $Logger
     * @param array                    $options
     * @return \Synergy\Project\ProjectAbstract
     * @throws \Synergy\Exception\InvalidProjectTypeException
     */
    public static function build(
            $projectName,
            $projectType,
            \Synergy\Logger\LoggerInterface $Logger,
            array $options = array()
        )
    {
        Project::init();
        Project::setLogger($Logger);
        Project::setName($projectName);
        Project::setType($projectType);
        Project::setOptions($options);
        return Project::getObject();
    }


}