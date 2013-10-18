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

namespace Synergy\Controller;

use Synergy\Exception\InvalidArgumentException;
use Synergy\Object;
use Synergy\Project;
use Synergy\Project\Web\WebRequest;
use Synergy\Project\ProjectAbstract;

/**
 * Class Controller
 *
 * @category Synergy\Controller
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Controller extends Object
{

    /**
     * @var WebRequest
     */
    protected $request;
    /**
     * @var \Synergy\Project\ProjectAbstract
     */
    protected $project;


    /**
     * Set the value of request member
     *
     * @param WebRequest $request
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setRequest(WebRequest $request)
    {
        if ($request instanceof WebRequest) {
            $this->request = $request;
        } else {
            throw new InvalidArgumentException(
                '$request must be an instance of \Synergy\Project\Web\WebRequest'
            );
        }
    }


    /**
     * Value of member request
     *
     * @return WebRequest value of member
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Set the value of project member
     *
     * @param ProjectAbstract $project
     *
     * @return void
     */
    public function setProject(ProjectAbstract $project)
    {
        $this->project = $project;
    }


    /**
     * Value of member project
     *
     * @return ProjectAbstract value of member
     */
    public function getProject()
    {
        return $this->project;
    }


    /**
     * If a config option with the keyname exists then return the value
     * doing any variable substitution first
     *
     * @param string $keyname eg synergy:webproject:template_dir
     *
     * @return bool|mixed
     */
    public function getOption($keyname)
    {
        return $this->project->getOption($keyname);
    }

}