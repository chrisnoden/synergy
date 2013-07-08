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
     * Sends the asset straight to the browser and exits
     *
     * @return void
     */
    public function deliver()
    {
        $extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));

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
                if ($this->filename) {
                    $file = escapeshellarg($this->filename);
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
        $aHeaders['Last-Modified'] = date('r', filectime($this->filename));
        $aHeaders['ETag'] = md5(filectime($this->filename));

        // now, finally, we send the headers
        foreach ($aHeaders AS $name => $value) {
            if ($value === false) continue;
            $hdr = sprintf('%s: %s', $name, $value);
            header($hdr);
        }
        header('Content-Length: ' . filesize($this->filename));
        if (isset($ctype)) {
            header('Content-Type: '.$ctype);
        }
        header('X-Filename: '. $this->filename);

        $fp = fopen($this->filename, 'rb');
        fpassthru($fp);

        exit;
    }


}