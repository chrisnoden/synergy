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

namespace Synergy\Project\Web\Template;

use Synergy\Exception\SynergyException;

/**
 * Class HtmlTemplate
 *
 * @category Synergy\Project\Web\Template
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class HtmlTemplate extends TemplateAbstract
{

    /**
     * Initialise
     *
     * @return void
     */
    protected function initTemplateEngine()
    {
    }


    /**
     * template render output
     *
     * @return string template render output
     * @throws SynergyException
     */
    protected function getRender()
    {
        $filename = $this->templateDir . DIRECTORY_SEPARATOR . $this->templateFile;
        if (file_exists($filename) && is_readable($filename)) {
            $render = file_get_contents($filename);
            return $render;
        } else {
            throw new SynergyException(
                sprintf(
                    'Invalid call to %s without setting templateFile or templateFile not readable',
                    __METHOD__
                )
            );
        }
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
    }

}