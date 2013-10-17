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

namespace Synergy\View;

use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\SynergyException;
use Synergy\Object;
use Synergy\Project\Web\WebResponse;
use Synergy\Tools\Tools;

/**
 * Class TemplateAbstract
 *
 * @category Synergy\Project\Web
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
abstract class TemplateAbstract extends Object
{

    /**
     * @var string path to the root template dir
     */
    protected $templateDir;
    /**
     * @var string path to the default template dir set by the Project
     */
    protected $projectTemplateDir;
    /**
     * @var string relative filename of the template on the filesystem
     */
    protected $templateFile;
    /**
     * @var array associative array of parameters to pass to the template
     */
    protected $parameters = array();
    /**
     * @var string path to the cache directory
     */
    protected $cacheDir;
    /**
     * @var bool is this a dev environment
     */
    protected $isDev = false;


    /**
     * Initialise the templating engine
     *
     * @return void
     */
    abstract protected function initTemplateEngine();


    /**
     * Initialise the template engine (if required)
     *
     * @return void
     */
    public function init()
    {
        $this->testTemplateFile();
        $this->initTemplateEngine();
    }


    /**
     * Get the WebResponse object from the template render
     *
     * @return WebResponse
     */
    public function getWebResponse()
    {
        $response = new WebResponse();
        $response->setContent($this->getRender());

        return $response;
    }


    /**
     * output from the template render
     *
     * @return string the output from the template render
     */
    abstract protected function getRender();


    /**
     * Location of the web templates directory
     *
     * @param string $dir absolute location of the web templates directory
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     * @return void
     */
    public function setTemplateDir($dir)
    {
        $this->templateDir = $dir;
    }


    /**
     * The default template directory
     *
     * @param string $dir
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     */
    public function setProjectTemplateDir($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory passed, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
            );
        } else {
            $this->projectTemplateDir = $dir;
        }
    }


    /**
     * Location of the templates directory used by this object
     * A concatenation of the projectTemplateDir and templateDir
     *
     * @return string location of the web templates
     */
    public function getTemplateDir()
    {
        if (isset($this->projectTemplateDir) && isset($this->templateDir)) {
            $testDir = $this->projectTemplateDir . DIRECTORY_SEPARATOR . $this->templateDir;
            if (is_dir($testDir)) {
                return $testDir;
            }
        } elseif (is_dir($this->templateDir)) {
            return $this->templateDir;
        }
        return $this->projectTemplateDir;
    }


    /**
     * Tests that the template file exists
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     * @throws \Synergy\Exception\SynergyException
     * @return void
     */
    protected function testTemplateFile()
    {
        if (!isset($this->templateFile)) {
            throw new SynergyException(
                'templateFile not set'
            );
        } else if (!isset($this->templateDir)) {
            throw new SynergyException(
                'templateDir not set'
            );
        }

        $testFile = $this->templateDir . DIRECTORY_SEPARATOR . $this->templateFile;
        if (!file_exists($testFile)) {
            throw new InvalidArgumentException(
                sprintf("Template File %s not found", $testFile)
            );
        } else if (!is_readable($testFile)) {
            throw new InvalidArgumentException(
                sprintf("Template File %s not readable", $testFile)
            );
        }
    }


    /**
     * Relative (to the templateDir) filename of the template
     *
     * @param string $filename relative filename of the template
     *
     * @throws \Synergy\Exception\InvalidArgumentException
     * @return void
     */
    public function setTemplateFile($filename)
    {
        $this->templateFile = $filename;
    }


    /**
     * relative filename of the template
     *
     * @return string relative filename of the template
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }


    /**
     * set the parameters for the template (variables)
     *
     * @param array $parameters parameters to pass to template
     *
     * @return void
     */
    public function setParameters(Array $parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * the parameters/variables for the template
     *
     * @return array associative array of variables/parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * Location of the template cache directory
     *
     * @param string $dir absolute location of the template cache directory
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public function setCacheDir($dir)
    {
        if (!is_dir($dir) && !Tools::mkdir($dir, false)) {
            throw new InvalidArgumentException(
                sprintf("Invalid directory, %s", $dir)
            );
        } else if (!is_readable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not readable", $dir)
            );
        } else if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                sprintf("Directory %s not writable", $dir)
            );
        } else {
            $this->cacheDir = $dir;
        }
    }


    /**
     * Location of the template cache directory
     *
     * @return string location of the web templates
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }


    /**
     * is this a dev project
     *
     * @param bool $isDev is this a dev project
     *
     * @return void
     */
    public function setDev($isDev)
    {
        $this->isDev = $isDev;
    }


    /**
     * Remove the entire template cache dir
     *
     * @return void
     */
    public function emptyCacheDir()
    {
        if (isset($this->cacheDir) && is_dir($this->cacheDir)) {
            Tools::removeDir($this->cacheDir);
        }
    }

}