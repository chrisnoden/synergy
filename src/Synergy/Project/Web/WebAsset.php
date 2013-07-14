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

namespace Synergy\Project\Web;

use Synergy\Exception\InvalidArgumentException;
use Synergy\Exception\SynergyException;
use Synergy\Object;
use Synergy\Project;

/**
 * Class WebAsset
 *
 * @category Synergy\Project\Web
 * @package  Synergy MVC Library
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class WebAsset extends Object
{

    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $contents;
    /**
     * @var string
     */
    protected $extension;
    /**
     * @var string
     */
    protected $contentType;
    /**
     * @var array
     */
    protected $aHeaders = array();
    /**
     * @var string
     */
    protected $status = '200 OK';


    public function __construct($filename = null)
    {
        if (!is_null($filename)) {
            $this->setFilename($filename);
        }
    }


    /**
     * Set the value of filename member
     *
     * @param string $filename
     *
     * @return void
     */
    public function setFilename($filename)
    {
        if (is_readable($filename)) {
            $this->filename = $filename;
            $extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
            try {
                $this->setExtension($extension);
            }
            catch (InvalidArgumentException $ex) {
                $file = escapeshellarg($this->filename);
                $this->contentType = shell_exec('file -bi ' . $file);
            }
        } else {
            throw new InvalidArgumentException(
                'Invalid asset filename '.$filename
            );
        }
    }


    /**
     * Value of member filename
     *
     * @return string value of member
     */
    public function getFilename()
    {
        return $this->filename;
    }


    /**
     * Set the value of contents member
     *
     * @param string $contents
     *
     * @return void
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }


    /**
     * Set the value of extension member
     *
     * @param string $extension
     *
     * @return void
     */
    public function setExtension($extension)
    {
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
        }

        if (isset($ctype)) {
            $this->contentType = $ctype;
        } else {
            throw new InvalidArgumentException(
                'Unsupported extension '.$extension
            );
        }
    }


    /**
     * Set the value of multiple headers
     *
     * @param array $aHeaders
     */
    public function setHeaders(Array $aHeaders)
    {
        $this->aHeaders = array_merge($this->aHeaders, $aHeaders);
    }


    /**
     * Set the value of an individual header
     *
     * @param string $header
     * @param string $value
     */
    public function addHeader($header, $value)
    {
        $this->aHeaders[$header] = $value;
    }


    /**
     * Send the HTTP headers
     */
    protected function sendHeaders()
    {
        header($_SERVER['SERVER_PROTOCOL'] .' '. $this->status);
        header('Status: '.$this->status);
        foreach ($this->aHeaders AS $header=>$value)
        {
            if ($value === false) continue;
            header(
                sprintf('%s: %s', $header, $value)
            );
        }
        header('Content-Type: '.$this->contentType);
    }


    /**
     * Sends the asset straight to the browser and exits
     *
     * @return void
     * @throws SynergyException
     */
    public function deliver()
    {
        if ((isset($this->filename) || isset($this->contents)) && isset($this->contentType)) {
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
            $aHeaders['Last-Modified'] = date('r', filectime($this->filename));
            $aHeaders['ETag'] = md5(filectime($this->filename));
            $aHeaders['Content-Length'] = filesize($this->filename);
            if (isset($this->filename) && !isset($this->contents)) {
                $aHeaders['X-Filename'] = $this->filename;
            }

            $this->setHeaders($aHeaders);
            $this->sendHeaders();

            if (isset($this->filename) && !isset($this->contents)) {
                $fp = fopen($this->filename, 'rb');
                fpassthru($fp);
                exit;
            } else {
                die ($this->contents);
            }
        }

        throw new SynergyException(
            'Invalid init of WebAsset, unable to deliver'
        );

    }

}