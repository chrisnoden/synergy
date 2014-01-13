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
    /**
     * @var array content-type values matched to file extensions
     */
    protected $aContentTypes = array(
        '3dm'     => 'x-world/x-3dmf',
        '3dmf'    => 'x-world/x-3dmf',
        'a'       => 'application/octet-stream',
        'aab'     => 'application/x-authorware-bin',
        'aam'     => 'application/x-authorware-map',
        'aas'     => 'application/x-authorware-seg',
        'abc'     => 'text/vnd.abc',
        'acgi'    => 'text/html',
        'afl'     => 'video/animaflex',
        'ai'      => 'application/postscript',
        'aif'     => 'audio/aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'aim'     => 'application/x-aim',
        'aip'     => 'text/x-audiosoft-intra',
        'ani'     => 'application/x-navi-animation',
        'aos'     => 'application/x-nokia-9000-communicator-add-on-software',
        'aps'     => 'application/mime',
        'arc'     => 'application/octet-stream',
        'arj'     => 'application/arj',
        'art'     => 'image/x-jg',
        'asf'     => 'video/x-ms-asf',
        'asm'     => 'text/x-asm',
        'asp'     => 'text/asp',
        'asx'     => 'video/x-ms-asf-plugin',
        'au'      => 'audio/x-au',
        'avi'     => 'video/avi',
        'avs'     => 'video/avs-video',
        'bcpio'   => 'application/x-bcpio',
        'bin'     => 'application/octet-stream',
        'bm'      => 'image/bmp',
        'bmp'     => 'image/x-windows-bmp',
        'boo'     => 'application/book',
        'book'    => 'application/book',
        'boz'     => 'application/x-bzip2',
        'bsh'     => 'application/x-bsh',
        'bz'      => 'application/x-bzip',
        'bz2'     => 'application/x-bzip2',
        'c'       => 'text/plain',
        'cat'     => 'application/vnd.ms-pki.seccat',
        'cc'      => 'text/plain',
        'ccad'    => 'application/clariscad',
        'cco'     => 'application/x-cocoa',
        'cdf'     => 'application/x-cdf',
        'cer'     => 'application/x-x509-ca-cert',
        'cha'     => 'application/x-chat',
        'chat'    => 'application/x-chat',
        'class'   => 'application/x-java-class',
        'com'     => 'application/octet-stream',
        'conf'    => 'text/plain',
        'cpio'    => 'application/x-cpio',
        'cpp'     => 'text/x-c',
        'cpt'     => 'application/x-cpt',
        'crl'     => 'application/pkcs-crl',
        'crt'     => 'application/x-x509-ca-cert',
        'csh'     => 'text/x-script.csh',
        'css'     => 'text/css',
        'csv'     => 'text/csv',
        'cxx'     => 'text/plain',
        'dcr'     => 'application/x-director',
        'deepv'   => 'application/x-deepv',
        'def'     => 'text/plain',
        'der'     => 'application/x-x509-ca-cert',
        'dif'     => 'video/x-dv',
        'dir'     => 'application/x-director',
        'dl'      => 'video/dl',
        'doc'     => 'application/msword',
        'dot'     => 'application/msword',
        'dp'      => 'application/commonground',
        'drw'     => 'application/drafting',
        'dump'    => 'application/octet-stream',
        'dv'      => 'video/x-dv',
        'dvi'     => 'application/x-dvi',
        'dwf'     => 'model/vnd.dwf',
        'dwg'     => 'image/vnd.dwg',
        'dxf'     => 'image/vnd.dwg',
        'dxr'     => 'application/x-director',
        'el'      => 'text/x-script.elisp',
        'elc'     => 'application/x-elc',
        'env'     => 'application/x-envoy',
        'eot'     => 'application/vnd.ms-fontobject',
        'eps'     => 'application/postscript',
        'es'      => 'application/x-esrehber',
        'etx'     => 'text/x-setext',
        'evy'     => 'application/x-envoy',
        'exe'     => 'application/octet-stream',
        'f'       => 'text/x-fortran',
        'f77'     => 'text/x-fortran',
        'f90'     => 'text/x-fortran',
        'fdf'     => 'application/vnd.fdf',
        'fif'     => 'image/fif',
        'fli'     => 'video/x-fli',
        'flo'     => 'image/florian',
        'flx'     => 'text/vnd.fmi.flexstor',
        'fmf'     => 'video/x-atomic3d-feature',
        'for'     => 'text/x-fortran',
        'fpx'     => 'image/vnd.fpx',
        'frl'     => 'application/freeloader',
        'funk'    => 'audio/make',
        'g'       => 'text/plain',
        'g3'      => 'image/g3fax',
        'gif'     => 'image/gif',
        'gl'      => 'video/x-gl',
        'gsd'     => 'audio/x-gsm',
        'gsm'     => 'audio/x-gsm',
        'gsp'     => 'application/x-gsp',
        'gss'     => 'application/x-gss',
        'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'gzip'    => 'application/x-gzip',
        'h'       => 'text/x-h',
        'hdf'     => 'application/x-hdf',
        'help'    => 'application/x-helpfile',
        'hgl'     => 'application/vnd.hp-hpgl',
        'hh'      => 'text/x-h',
        'hlp'     => 'application/x-helpfile',
        'hpg'     => 'application/vnd.hp-hpgl',
        'hpgl'    => 'application/vnd.hp-hpgl',
        'hqx'     => 'application/binhex',
        'hta'     => 'application/hta',
        'htc'     => 'text/x-component',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'htmls'   => 'text/html',
        'htt'     => 'text/webviewhtml',
        'htx'     => 'text/html',
        'ice'     => 'x-conference/x-cooltalk',
        'ico'     => 'image/vnd.microsoft.icon',
        'idc'     => 'text/plain',
        'ief'     => 'image/ief',
        'iefs'    => 'image/ief',
        'iges'    => 'model/iges',
        'igs'     => 'model/iges',
        'ima'     => 'application/x-ima',
        'imap'    => 'application/x-httpd-imap',
        'inf'     => 'application/inf',
        'ins'     => 'application/x-internett-signup',
        'ip'      => 'application/x-ip2',
        'isu'     => 'video/x-isvideo',
        'it'      => 'audio/it',
        'iv'      => 'application/x-inventor',
        'ivr'     => 'i-world/i-vrml',
        'ivy'     => 'application/x-livescreen',
        'jam'     => 'audio/x-jam',
        'jav'     => 'text/x-java-source',
        'java'    => 'text/x-java-source',
        'jcm'     => 'application/x-java-commerce',
        'jfif'    => 'image/jpeg',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'jps'     => 'image/x-jps',
        'js'      => 'application/x-javascript',
        'json'    => 'application/json',
        'jut'     => 'image/jutvision',
        'kar'     => 'audio/midi',
        'ksh'     => 'application/x-ksh',
        'la'      => 'audio/nspaudio',
        'lam'     => 'audio/x-liveaudio',
        'latex'   => 'application/x-latex',
        'lha'     => 'application/lha',
        'lhx'     => 'application/octet-stream',
        'list'    => 'text/plain',
        'lma'     => 'audio/nspaudio',
        'log'     => 'text/plain',
        'lsp'     => 'application/x-lisp',
        'lst'     => 'text/plain',
        'lsx'     => 'text/x-la-asf',
        'ltx'     => 'application/x-latex',
        'lzh'     => 'application/octet-stream',
        'lzx'     => 'application/lzx',
        'm'       => 'text/plain',
        'm1v'     => 'video/mpeg',
        'm2a'     => 'audio/mpeg',
        'm2v'     => 'video/mpeg',
        'm3u'     => 'audio/x-mpequrl',
        'man'     => 'application/x-troff-man',
        'map'     => 'application/x-navimap',
        'mar'     => 'text/plain',
        'mbd'     => 'application/mbedlet',
        'mc'      => 'application/x-magic-cap-package-1.0',
        'mcd'     => 'application/mcad',
        'mcf'     => 'image/vasa',
        'mcp'     => 'application/netmc',
        'me'      => 'application/x-troff-me',
        'mht'     => 'message/rfc822',
        'mhtml'   => 'message/rfc822',
        'mid'     => 'audio/midi',
        'midi'    => 'audio/x-midi',
        'mif'     => 'application/x-mif',
        'mime'    => 'message/rfc822',
        'mjf'     => 'audio/x-vnd.audioexplosion.mjuicemediafile',
        'mjpg'    => 'video/x-motion-jpeg',
        'mm'      => 'application/x-meme',
        'mme'     => 'application/base64',
        'mod'     => 'audio/x-mod',
        'moov'    => 'video/quicktime',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'video/mpeg',
        'mp3'     => 'audio/mpeg3',
        'mpa'     => 'audio/mpeg',
        'mpc'     => 'application/x-project',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpga'    => 'audio/mpeg',
        'mpp'     => 'application/vnd.ms-project',
        'mpt'     => 'application/x-project',
        'mpv'     => 'application/x-project',
        'mpx'     => 'application/x-project',
        'mrc'     => 'application/marc',
        'ms'      => 'application/x-troff-ms',
        'mv'      => 'video/x-sgi-movie',
        'my'      => 'audio/make',
        'mzz'     => 'application/x-vnd.audioexplosion.mzz',
        'nap'     => 'image/naplps',
        'naplps'  => 'image/naplps',
        'nc'      => 'application/x-netcdf',
        'ncm'     => 'application/vnd.nokia.configuration-message',
        'nif'     => 'image/x-niff',
        'niff'    => 'image/x-niff',
        'nix'     => 'application/x-mix-transfer',
        'nsc'     => 'application/x-conference',
        'nvd'     => 'application/x-navidoc',
        'o'       => 'application/octet-stream',
        'oda'     => 'application/oda',
        'omc'     => 'application/x-omc',
        'omcd'    => 'application/x-omcdatamaker',
        'omcr'    => 'application/x-omcregerator',
        'otf'     => 'application/x-font-opentype',
        'p'       => 'text/x-pascal',
        'p10'     => 'application/x-pkcs10',
        'p12'     => 'application/x-pkcs12',
        'p7a'     => 'application/x-pkcs7-signature',
        'p7c'     => 'application/x-pkcs7-mime',
        'p7m'     => 'application/pkcs7-mime',
        'p7r'     => 'application/x-pkcs7-certreqresp',
        'p7s'     => 'application/pkcs7-signature',
        'part'    => 'application/pro_eng',
        'pas'     => 'text/pascal',
        'pbm'     => 'image/x-portable-bitmap',
        'pcl'     => 'application/x-pcl',
        'pct'     => 'image/x-pict',
        'pcx'     => 'image/x-pcx',
        'pdb'     => 'chemical/x-pdb',
        'pdf'     => 'application/pdf',
        'pfunk'   => 'audio/make.my.funk',
        'pgm'     => 'image/x-portable-greymap',
        'pic'     => 'image/pict',
        'pict'    => 'image/pict',
        'pkg'     => 'application/x-newton-compatible-pkg',
        'pko'     => 'application/vnd.ms-pki.pko',
        'pl'      => 'text/x-script.perl',
        'plx'     => 'application/x-pixclscript',
        'pm'      => 'text/x-script.perl-module',
        'pm4'     => 'application/x-pagemaker',
        'pm5'     => 'application/x-pagemaker',
        'png'     => 'image/png',
        'pnm'     => 'image/x-portable-anymap',
        'pot'     => 'application/vnd.ms-powerpoint',
        'pov'     => 'model/x-pov',
        'ppa'     => 'application/vnd.ms-powerpoint',
        'ppm'     => 'image/x-portable-pixmap',
        'pps'     => 'application/vnd.ms-powerpoint',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'ppz'     => 'application/mspowerpoint',
        'pre'     => 'application/x-freelance',
        'prt'     => 'application/pro_eng',
        'ps'      => 'application/postscript',
        'psd'     => 'application/octet-stream',
        'pvu'     => 'paleovu/x-pv',
        'pwz'     => 'application/vnd.ms-powerpoint',
        'py'      => 'text/x-script.phyton',
        'pyc'     => 'applicaiton/x-bytecode.python',
        'qcp'     => 'audio/vnd.qcelp',
        'qd3'     => 'x-world/x-3dmf',
        'qd3d'    => 'x-world/x-3dmf',
        'qif'     => 'image/x-quicktime',
        'qt'      => 'video/quicktime',
        'qtc'     => 'video/x-qtc',
        'qti'     => 'image/x-quicktime',
        'qtif'    => 'image/x-quicktime',
        'ra'      => 'audio/x-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rast'    => 'image/cmu-raster',
        'rexx'    => 'text/x-script.rexx',
        'rf'      => 'image/vnd.rn-realflash',
        'rgb'     => 'image/x-rgb',
        'rm'      => 'audio/x-pn-realaudio',
        'rmi'     => 'audio/mid',
        'rmm'     => 'audio/x-pn-realaudio',
        'rmp'     => 'audio/x-pn-realaudio-plugin',
        'rng'     => 'application/vnd.nokia.ringing-tone',
        'rnx'     => 'application/vnd.rn-realplayer',
        'roff'    => 'application/x-troff',
        'rp'      => 'image/vnd.rn-realpix',
        'rpm'     => 'audio/x-pn-realaudio-plugin',
        'rt'      => 'text/vnd.rn-realtext',
        'rtf'     => 'text/richtext',
        'rtx'     => 'text/richtext',
        'rv'      => 'video/vnd.rn-realvideo',
        's'       => 'text/x-asm',
        's3m'     => 'audio/s3m',
        'saveme'  => 'application/octet-stream',
        'sbk'     => 'application/x-tbook',
        'scm'     => 'video/x-scm',
        'sdml'    => 'text/plain',
        'sdp'     => 'application/x-sdp',
        'sdr'     => 'application/sounder',
        'sea'     => 'application/x-sea',
        'set'     => 'application/set',
        'sgm'     => 'text/x-sgml',
        'sgml'    => 'text/x-sgml',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'shtml'   => 'text/x-server-parsed-html',
        'sid'     => 'audio/x-psid',
        'sit'     => 'application/x-stuffit',
        'skd'     => 'application/x-koan',
        'skm'     => 'application/x-koan',
        'skp'     => 'application/x-koan',
        'skt'     => 'application/x-koan',
        'sl'      => 'application/x-seelogo',
        'smi'     => 'application/smil',
        'smil'    => 'application/smil',
        'snd'     => 'audio/basic',
        'sol'     => 'application/solids',
        'spc'     => 'application/x-pkcs7-certificates',
        'spl'     => 'application/futuresplash',
        'spr'     => 'application/x-sprite',
        'sprite'  => 'application/x-sprite',
        'src'     => 'application/x-wais-source',
        'ssi'     => 'text/x-server-parsed-html',
        'ssm'     => 'application/streamingmedia',
        'sst'     => 'application/vnd.ms-pki.certstore',
        'step'    => 'application/step',
        'stl'     => 'application/sla',
        'stp'     => 'application/step',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        'svf'     => 'image/vnd.dwg',
        'svg'     => 'image/svg+xml',
        'svr'     => 'application/x-world',
        'swf'     => 'application/x-shockwave-flash',
        't'       => 'application/x-troff',
        'talk'    => 'text/x-speech',
        'tar'     => 'application/x-tar',
        'tbk'     => 'application/toolbook',
        'tcl'     => 'application/x-tcl',
        'tcsh'    => 'text/x-script.tcsh',
        'tex'     => 'application/x-tex',
        'texi'    => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'text'    => 'text/plain',
        'tgz'     => 'application/x-compressed',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'tr'      => 'application/x-troff',
        'tsi'     => 'audio/tsp-audio',
        'tsp'     => 'application/dsptype',
        'tsv'     => 'text/tab-separated-values',
        'ttf'     => 'application/x-font-ttf',
        'turbot'  => 'image/florian',
        'txt'     => 'text/plain',
        'uil'     => 'text/x-uil',
        'uni'     => 'text/uri-list',
        'unis'    => 'text/uri-list',
        'unv'     => 'application/i-deas',
        'uri'     => 'text/uri-list',
        'uris'    => 'text/uri-list',
        'ustar'   => 'application/x-ustar',
        'uu'      => 'application/octet-stream',
        'uue'     => 'text/x-uuencode',
        'vcd'     => 'application/x-cdlink',
        'vcs'     => 'text/x-vcalendar',
        'vda'     => 'application/vda',
        'vdo'     => 'video/vdo',
        'vew'     => 'application/groupwise',
        'viv'     => 'video/vivo',
        'vivo'    => 'video/vivo',
        'vmd'     => 'application/vocaltec-media-desc',
        'vmf'     => 'application/vocaltec-media-file',
        'voc'     => 'audio/voc',
        'vos'     => 'video/vosaic',
        'vox'     => 'audio/voxware',
        'vqe'     => 'audio/x-twinvq-plugin',
        'vqf'     => 'audio/x-twinvq',
        'vql'     => 'audio/x-twinvq-plugin',
        'vrml'    => 'application/x-vrml',
        'vrt'     => 'x-world/x-vrt',
        'vsd'     => 'application/x-visio',
        'vst'     => 'application/x-visio',
        'vsw'     => 'application/x-visio',
        'w60'     => 'application/wordperfect6.0',
        'w61'     => 'application/wordperfect6.1',
        'w6w'     => 'application/msword',
        'wav'     => 'audio/wav',
        'wb1'     => 'application/x-qpro',
        'wbmp'    => 'image/vnd.wap.wbmp',
        'web'     => 'application/vnd.xara',
        'wiz'     => 'application/msword',
        'wk1'     => 'application/x-123',
        'wmf'     => 'windows/metafile',
        'wml'     => 'text/vnd.wap.wml',
        'wmlc'    => 'application/vnd.wap.wmlc',
        'wmls'    => 'text/vnd.wap.wmlscript',
        'wmlsc'   => 'application/vnd.wap.wmlscriptc',
        'woff'    => 'application/font-woff',
        'word'    => 'application/msword',
        'wp'      => 'application/wordperfect',
        'wp5'     => 'application/wordperfect',
        'wp6'     => 'application/wordperfect',
        'wpd'     => 'application/wordperfect',
        'wq1'     => 'application/x-lotus',
        'wri'     => 'application/mswrite',
        'wrl'     => 'application/x-world',
        'wrz'     => 'model/vrml',
        'wsc'     => 'text/scriplet',
        'wsrc'    => 'application/x-wais-source',
        'wtk'     => 'application/x-wintalk',
        'xbm'     => 'image/x-xbitmap',
        'xdr'     => 'video/x-amt-demorun',
        'xgz'     => 'xgl/drawing',
        'xif'     => 'image/vnd.xiff',
        'xl'      => 'application/excel',
        'xla'     => 'application/excel',
        'xlb'     => 'application/excel',
        'xlc'     => 'application/excel',
        'xld'     => 'application/excel',
        'xlk'     => 'application/excel',
        'xll'     => 'application/excel',
        'xlm'     => 'application/excel',
        'xls'     => 'application/excel',
        'xlt'     => 'application/excel',
        'xlv'     => 'application/excel',
        'xlw'     => 'application/excel',
        'xm'      => 'audio/xm',
        'xml'     => 'text/xml',
        'xmz'     => 'xgl/movie',
        'xpix'    => 'application/x-vnd.ls-xpix',
        'xpm'     => 'image/x-xpixmap',
        'x'       => 'png	image/png',
        'xsr'     => 'video/x-amt-showrun',
        'xwd'     => 'image/x-xwd',
        'xyz'     => 'chemical/x-pdb',
        'z'       => 'application/x-compress',
        'zip'     => 'application/zip',
        'zoo'     => 'application/octet-stream',
        'zsh'     => 'text/x-script.zsh',
    );


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
     * @throws InvalidArgumentException
     */
    public function setFilename($filename)
    {
        if (is_readable($filename)) {
            $this->filename = $filename;
            $extension      = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
            try {
                $this->setExtension($extension);
            } catch (InvalidArgumentException $ex) {
                $file              = escapeshellarg($this->filename);
                $this->contentType = shell_exec('file -bi ' . $file);
            }
        } else {
            throw new InvalidArgumentException(
                'Invalid asset filename ' . $filename
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
     * Set the content of the asset
     *
     * @param string $contents
     *
     * @return void
     */
    public function setContent($contents)
    {
        $this->contents = $contents;
    }


    /**
     * Set the value of extension member
     *
     * @param string $extension
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setExtension($extension)
    {
        // Determine Content Type
        $extension = strtolower(trim($extension));
        if (isset($this->aContentTypes[$extension])) {
            $this->contentType = $this->aContentTypes[$extension];
        } else {
            throw new InvalidArgumentException(
                'Unsupported extension ' . $extension
            );
        }
    }


    /**
     * Set the value of multiple headers replacing any that are already set
     *
     * @param array $aHeaders
     */
    public function setHeaders(Array $aHeaders)
    {
        $this->aHeaders = array_merge($this->aHeaders, $aHeaders);
    }


    /**
     * Set the value of status member
     *
     * @param string $status
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (strlen($status) < 3) {
            throw new InvalidArgumentException(
                'Invalid status'
            );
        }

        $statii = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Long",
            415 => "Unsupported Media Type",
            416 => "Requested Range Not Satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Timeout",
            505 => "HTTP Version Not Supported"
        );

        if (isset($statii[$status])) {
            $this->status = sprintf("%s %s", $status, $statii[$status]);
        } else {
            $this->status = $status;
        }
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
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->status);
        header('Status: ' . $this->status);
        foreach ($this->aHeaders AS $header => $value) {
            if ($value === false) continue;
            header(
                sprintf('%s: %s', $header, $value)
            );
        }
        header('Content-Type: ' . $this->contentType);
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
                    'Expires'       => date('r', strtotime('Yesterday')),
                    'Cache-Control' => 'no-store, no-cache, max-age=0, must-revalidate',
                    'Pragma'        => 'no-cache'
                );
            } else {
                $aHeaders = array(
                    'Expires'       => date('r', strtotime('+5 min')),
                    'Cache-Control' => 'private, max-age=300, must-revalidate',
                    'Pragma'        => 'private'
                );
            }
            // Important headers
            if (isset($this->filename) && !isset($this->contents)) {
                $aHeaders['X-Filename'] = $this->filename;
                $aHeaders['Last-Modified']  = date('r', filectime($this->filename));
                $aHeaders['ETag']           = md5(filectime($this->filename));
                $aHeaders['Content-Length'] = filesize($this->filename);
            } else {
                $aHeaders['Last-Modified']  = date('r');
                $aHeaders['Content-Length'] = strlen($this->contents);
            }

            $this->aHeaders = array_merge($aHeaders, $this->aHeaders);
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