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

use Symfony\Component\HttpFoundation\Response;
use Synergy\Logger\Logger;
use Synergy\Project\Web\Template\SmartyTemplate;
use Synergy\Project\Web\Template\TwigTemplate;
use Synergy\Project\Web\Template\TemplateAbstract;
use Synergy\Project\Web\Template\HtmlTemplate;
use Synergy\Project\Web\WebRequest;
use Synergy\Exception\InvalidArgumentException;
use Synergy\Object;
use Synergy\Project;

/**
 * Class Controller
 *
 * @category Synergy\Controller
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class Controller extends Object implements ControllerInterface
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;


    /**
     * Default action to be inherited by your own controller code
     *
     * @return void
     */
    public function defaultAction()
    {
    }


    /**
     * Find a template or asset that can fulfil our request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return TemplateAbstract|void returns a template or null
     */
    protected function requestMatch($request = null)
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
                $this->matchAsset($matchDir, $file);
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
        $testfile = $matchDir . $file;
        if (file_exists($testfile)) {
            Logger::info('HTML: '.$file);
            $template = new HtmlTemplate();
            $template->setTemplateDir($matchDir);
            $template->setTemplateFile($file);
            return $template;
        } else if (file_exists($testfile . '.tpl')) {
            Logger::info('Smarty Template: '.$file.'.tpl');
            $template = new SmartyTemplate();
            $template->setTemplateDir(dirname($testfile));
            $template->setTemplateFile(basename($testfile) . '.tpl');
            return $template;
        } else if (file_exists($testfile . '.twig')) {
            Logger::info('Twig Template: '.$file.'.twig');
            $template = new TwigTemplate();
            $template->setTemplateDir(dirname($testfile));
            $template->setTemplateFile(basename($testfile) . '.twig');
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
            return $this->matchTemplate($rootDir, $path . DIRECTORY_SEPARATOR . 'index.html');
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
     */
    protected function matchAsset($matchDir, $file)
    {
        if (substr($file, 0, 11) == '/_synergy_/') {
            // internal asset request
            $matchDir = SYNERGY_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'View';
            $file = substr($file, 10);
        }
        $testfile = $matchDir . $file;
        if (file_exists($testfile) && is_readable($testfile)) {
            Logger::debug("Asset found: $file");
            $this->deliverAsset($testfile);
        }
    }


    /**
     * Sends the asset straight to the browser and exits
     *
     * @param string $filename the full path and name of the asset to deliver
     */
    protected function deliverAsset($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Determine Content Type
        switch ($extension) {
            case 'pdf':
                $ctype = 'application/pdf';
                break;
            case 'exe':
                $ctype = 'application/octet-stream';
                break;
            case 'zip':
                $ctype = 'application/zip';
                break;
            case 'doc':
                $ctype = 'application/msword';
                break;
            case 'xls':
                $ctype = 'application/vnd.ms-excel';
                break;
            case 'ppt':
                $ctype = 'application/vnd.ms-powerpoint';
                break;
            case 'gif':
                $ctype = 'image/gif';
                break;
            case 'png':
                $ctype = 'image/png';
                break;
            case 'jpeg':
            case 'jpg':
                $ctype = 'image/jpeg';
                break;
            case 'js':
                $ctype = 'application/x-javascript';
                break;
            case 'json':
                $ctype = 'application/json';
                break;
            case 'css':
                $ctype = 'text/css';
                break;
            case 'xml':
                $ctype = 'text/xml';
                break;
            case 'txt':
                $ctype = 'text/plain';
                break;
            case 'htm':
            case 'html':
                $ctype = 'text/html';
                break;
            case 'ico':
                $ctype = 'image/vnd.microsoft.icon';
                break;
            case 'svg':
                $ctype = 'image/svg+xml';
                break;
            case 'ttf':
                $ctype = 'application/x-font-ttf';
                break;
            case 'otf':
                $ctype = 'application/x-font-opentype';
                break;
            case 'woff':
                $ctype = 'application/font-woff';
                break;
            case 'eot':
                $ctype = 'application/vnd.ms-fontobject';
                break;
            default:
                if ($filename) {
                    $file = escapeshellarg($filename);
                    $ctype = shell_exec('file -bi ' . $file);
                }
                break;
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        header('Status: 200 OK');
        if (Project::isDev()) {
            $aHeaders = array(
                'Expires' => date('r', strtotime('Yesterday')),
                'Cache-Control' => 'no-store, no-cache, max-age=0, must-revalidate',
                'Pragma' => 'no-cache'
            );
        } else {
            $aHeaders = array(
                'Expires' => date('r', strtotime('+5 min')),
                'Cache-Control' => 'private, max-age=300, must-revalidate',
                'Pragma' => 'private'
            );
        }
        // Important headers
        $aHeaders['Last-Modified'] = date('r', filectime($filename));
        $aHeaders['ETag'] = md5(filectime($filename));

        // now, finally, we send the headers
        foreach ($aHeaders AS $name => $value) {
            if ($value === false) continue;
            $hdr = sprintf('%s: %s', $name, $value);
            header($hdr);
        }
        header('Content-Length: ' . filesize($filename));
        if (isset($ctype)) {
            header('Content-Type: '.$ctype);
        }
        header('X-Filename: '. $filename);

        $fp = fopen($filename, 'rb');
        fpassthru($fp);

        exit;
    }


    /**
     * Set the value of request member
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function setRequest($request)
    {
        if ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            $this->request = $request;
        } else {
            throw new InvalidArgumentException(
                '$request must be an instance of \Symfony\Component\HttpFoundation\Request'
            );
        }
    }


    /**
     * Value of member request
     *
     * @return \Symfony\Component\HttpFoundation\Request value of member
     */
    public function getRequest()
    {
        return $this->request;
    }

}