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

namespace Synergy\Controller;

use Synergy\Logger\Logger;
use Synergy\Project\Web\WebAsset;
use Synergy\Project\Web\WebRequest;
use Synergy\View\TemplateAbstract;
use Synergy\View\HtmlTemplate;
use Synergy\View\SmartyTemplate;
use Synergy\View\TwigTemplate;

/**
 * Class SmartController
 * Searches for a template that can fulfil your request
 *
 * @category Synergy\Controller
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SmartController extends Controller
{


    /**
     * Find a template or asset that can fulfil our request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return TemplateAbstract|void returns a template or null
     */
    public function requestMatch($request = null)
    {
        if (is_a($request, '\Synergy\Project\Web\WebRequest') || ($this->request instanceof WebRequest && $request = $this->request)) {
            return $this->matchWebFile($request->getTemplateDir(), $request->getPathInfo());
        } else {
            return;
        }
    }


    /**
     * Looks for a web file (template or asset) matching the
     * $file in the matchDir (or below)
     *
     * @param string $matchDir
     * @param string $file
     *
     * @return SmartyTemplate|void
     */
    protected function matchWebFile($matchDir, $file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($extension)
        {
            case 'htm':
            case 'html':
                return $this->matchTemplate($matchDir, $file);
                break;

            case '':
                return $this->matchDirectory($matchDir, $file);
                break;

            default:
                return $this->matchAsset($matchDir, $file);
        }
    }


    /**
     * Look for a template file within the matchDir
     *
     * @param string $matchDir
     * @param string $file
     *
     * @return SmartyTemplate|TwigTemplate|null
     */
    protected function matchTemplate($matchDir, $file)
    {
        if (file_exists($matchDir . $file . '.tpl') && class_exists('\Smarty')) {
            Logger::info('Smarty Template: ' . $file . '.tpl');
            $template = new SmartyTemplate();
            $template->setTemplateDir($matchDir);
            $template->setTemplateFile($file . '.tpl');
            return $template;
        } elseif (file_exists($matchDir . $file . '.twig') && class_exists('\Twig_Environment')) {
            Logger::info('Twig Template: ' . $file . '.twig');
            $template = new TwigTemplate();
            $template->setTemplateDir($matchDir);
            $template->setTemplateFile($file . '.twig');
            return $template;
        } elseif (file_exists($matchDir . $file)) {
            Logger::info('HTML: ' . $file);
            $template = new HtmlTemplate();
            $template->setTemplateDir($matchDir);
            $template->setTemplateFile($file);
            return $template;
        }
    }


    /**
     * Try to match to an index.html template in the given path
     *
     * @param string $rootDir
     * @param string $path
     *
     * @return SmartyTemplate
     */
    protected function matchDirectory($rootDir, $path)
    {
        $testdir = $rootDir . $path;
        if (is_dir($testdir)) {
            if (substr($testdir, strlen($testdir)-1) != '/') {
                header("Location: ".$path.'/');
                exit;
            }
            return $this->matchTemplate($rootDir, $path . 'index.html');
        }

        $response = $this->matchTemplate($rootDir, $path . '.html');
        if ($response) {
            return $response;
        }
    }


    /**
     * Try to match an asset file in our templateDir
     *
     * @param string $matchDir
     * @param string $file
     *
     * @return WebAsset|void
     */
    protected function matchAsset($matchDir, $file)
    {
        if (strpos($file, '_synergy_')) {
            // internal asset request
            $matchDir = SYNERGY_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . '_synergy_';
            $file = substr($file, 10);
        }
        $testfile = $matchDir . $file;
        if (file_exists($testfile) && is_readable($testfile)) {
            $asset = new WebAsset($testfile);
            Logger::debug("Asset found: $file");
            return $asset;
        }
    }


}