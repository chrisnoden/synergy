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

use Synergy\Exception\SynergyException;
use Synergy\Logger\Logger;
use Synergy\Project\ProjectAbstract;

/**
 * Class SmartyTemplate
 *
 * @category Synergy\View\Template
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SmartyTemplate extends TemplateAbstract
{

    /**
     * @var \Smarty
     */
    private $_loader;


    /**
     * Initialise Twig
     *
     * @return void
     */
    protected function initTemplateEngine()
    {
        $this->_loader = new \Smarty();
        $this->_loader->muteExpectedErrors();
        $this->_loader->setTemplateDir($this->templateDir);
        $this->_loader->addPluginsDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SmartyPlugins');
        $this->_initSmartyCache();
        Logger::debug("Smarty Cache: ".$this->_loader->getCacheDir());
    }


    /**
     * template render output
     *
     * @return string template render output
     * @throws SynergyException
     */
    protected function getRender()
    {
        if (isset($this->templateFile)) {
            $this->assignSmartyVariables();
            $render = $this->_loader->fetch($this->templateFile);
            return $render;
        } else {
            throw new SynergyException(
                sprintf(
                    'Invalid call to %s without setting templateFile',
                    __METHOD__
                )
            );
        }
    }


    /**
     * Set the variables so Smarty can access them
     *
     * @return void
     */
    protected function assignSmartyVariables()
    {
        $this->_loader->assign($this->parameters);
    }


    /**
     * Location of the template cache directory
     *
     * @param string $dir absolute location of the template cache directory
     *
     * @return void
     */
    public function setCacheDir($dir)
    {
        $dir .= DIRECTORY_SEPARATOR . 'smarty';
        Logger::debug("Smarty cache dir set to: ".$dir);
        parent::setCacheDir($dir);
    }


    /**
     * Prepares the cache folder for Smarty
     *
     * @return void
     */
    private function _initSmartyCache()
    {
        if (!is_dir($this->cacheDir)) {
            $this->mkdir($this->cacheDir, true);
        }

        // compiled templates dir
        $path
            = $this->cacheDir .
            DIRECTORY_SEPARATOR .
            'templates_c' .
            DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            $this->mkdir($path, false);
        }
        $this->_loader->setCompileDir($path);

        // cache dir
        $path
            = $this->cacheDir .
            DIRECTORY_SEPARATOR .
            'cache' .
            DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            $this->mkdir($path, false);
        }
        $this->_loader->setCacheDir($path);

        // configs dir
        $path
            = $this->cacheDir .
            DIRECTORY_SEPARATOR .
            'configs' .
            DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            $this->mkdir($path, false);
        }
        $this->_loader->setConfigDir($path);

    }

}