<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Project;

use Psr\Log\LogLevel;
use Synergy\Exception\ProjectException;
use Synergy\Logger\Logger;
use Synergy\Project;
use Symfony\Component\HttpFoundation\Request;
use Synergy\Web\Router;
use Synergy\Project\ProjectAbstract;

/**
 * Handle web template calls
 */
final class WebProject extends ProjectAbstract
{

    /**
     * Array of extensions that we handle for Smarty magic
     *
     * @var array
     */
    private $_aHandledExtensions = array();
    /**
     * Path of the root website folder
     * eg /opt/local/www/site/public_html
     *
     * @var string
     */
    private $_serverWebFolder;
    /**
     * @var string path where our web designers will stick their "HTML" templates
     */
    private $_templatePath;
    /**
     * @var string absolute filename of the template
     */
    private $_templateFileName;
    /**
     * @var string relative filename of the error document shown if a 404/403 is needed
     */
    private $_errorDocFile;
    /**
     * @var string path where the error doc template resides (defaults to _templatePath)
     */
    private $_errorDocPath;
    /**
     * @var bool is this request for a resource (whereupon we don't do much logging)
     */
    private $_bResource = false;
    /**
     * @var string filename of the resource if found
     */
    private $_sResourceFilename;
    /**
     * @var bool is the resource protected from leaching
     */
    private $_bResourceProtected = false;
    /**
     * associate array of variables to be passed to the template
     *
     * @var array
     */
    private $_aTemplateVariables = array();
    /**
     * @var array of controller objects we need to call
     */
    private $_aControllers = array();
    /**
     * @var array associative array of additional or replacement HTTP 200 response headers
     */
    private $_aHttp200Headers = array();
    /**
     * @var bool is the project prepared
     */
    private $_bIsPrepared = false;
    /**
     * @var \SimpleXMLElement
     */
    private $_oSiteConfig;
    /**
     * @var \SimpleXMLElement
     */
    private $_oWebConfig;
    /**
     * @var array resource paths from our site config
     */
    private $_aResourcePaths = array();
    /**
     * @var string the session ID
     */
    private $_sessionID;
    /**
     * @var bool do we need to do our own URL rewriting
     */
    private $_bSessionUrlRewriting = false;
    /**
     * @var bool are we allowed to use cookies
     */
    private $_bCookiesOK = false;
    /**
     * @var bool are we OK to use sessions
     */
    private $_bSessionsOK = false;
    /**
     * @var string name of the cookie used to save acceptance of cookies
     */
    private $_optInCookieName = 'sal_CookieOptIn';
    /**
     * @var string domain of the opt in cookie
     */
    private $_optInCookieDomain;
    /**
     * @var string
     */
    private $_sessionPolicy = 'default';
    /**
     * @var \Mobile_Detect
     */
    private $_oDevice;
    /**
     * @var \UASparser
     */
    private $_aUserAgentInfo;
    /**
     * @var string the contents of the view
     */
    private $_viewContents;
    /**
     * @var string the document type for the view output
     */
    private $_viewDocType = 'html';
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $_oRequest;
    /**
     * @var RootController
     */
    private $_oController;


    /**
     * Instantiate a new Web_Handler object
     */
    public function __construct()
    {
        // turn off automatic session starting (if enabled)
        ini_set('session.auto_start', '0');
        // @todo check disabling auto_start actually stops the session before it's been created

        parent::__construct();

        /**
         * configure our array of handled extensions - file extensions that we
         * will serve a (view) HTML page. This is an array of extensions which
         * are not resources (ie not images, zips, downloads, etc) similar to
         * the project configurable extensions which define what the template
         * extensions might be rather than the incoming (controller) side of
         * things which these address
         */
        $this->_aHandledExtensions = array(
            'html', 'htm', 'php'
        );

    }


    /**
     * destructor - cleans up where necessary
     */
    public function __destruct()
    {
        parent::__destruct();
    }


    /**
     * Our main method : let's go and run our web project
     */
    public function launch()
    {
        $this->_oRequest = Request::createFromGlobals();
        /**
         * Choose and load our Controller
         */
        $this->getController();
//        var_dump($this->_oController); // @todo remove this
    }


    /**
     * Choose the Controller for the web request
     */
    public function getController()
    {
        $router = new Router();
        $this->_oController = $router->chooseController($this->_oRequest->getRequestUri());
    }


    /**
     * Prepare our Project
     */
    public function prepare()
    {

        parent::prepare();

        // Load the Web_Project config block
        try {
            $this->loadConfig();
        }
        catch (SalUnsupportedSiteException $ex)
        {
            Logger::log(LogLevel::WARNING, 'Unsupported request for hostname: '.$this->_oRequest->getDomain());
            $this->sendErrorHeaders();
            $this->sendErrorDocument('404 : Unsupported request');
            exit;
        }

        // Has the user opted in to our use of cookies
        $this->checkSessionPolicy();

        // locate the path where the template files live
        $this->setTemplatePath();

        // locate any custom error document
        $this->setErrorDocFile();

        // Start our session management
        $this->initSessionHandling();

        // Check if the request is for an admin resource
        $this->checkForAdminResource();

        // Run our mobile device detection
        $this->mobileDetect();

        // Load any resource paths from the config
        $this->loadResourcePaths();

        // Are we possibly looking for a resource file
        $this->isResourceRequest($this->_oRequest->getDocument());

        // do we need to use any controllers
        $this->findControllers();

        // pre-launch execution of any controllers
        $this->prepControllers();

        // set a flag to confirm we're ready to launch
        $this->_bIsPrepared = true;
    }


    /**
     * Main method to start the SAL platform working
     */
    public function execute()
    {
        if (!$this->_bIsPrepared) {
            throw new ProjectException('Project needs to call prepare() method before execute()');
        }

        // If we have identified a resource file then deliver it and exit
        if ($this->_bResource) {
            if (isset($this->_sResourceFilename) && file_exists($this->_sResourceFilename) && is_readable($this->_sResourceFilename) && ($this->_bResourceProtected === false || isset($_SESSION['clientAuth']) || (isset($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'], $this->_oRequest->getDomain())))) {
                $this->sendResourceFile();
                exit;
            } else if (!isset($this->_sResourceFilename) || (!file_exists($this->_sResourceFilename) || !is_readable($this->_sResourceFilename)) && !isset($this->_viewContents)) {
                if (strtolower($this->_oRequest->getDocument()) == '/favicon.ico') {
                    $this->_sResourceFilename = SAL_PLATFORM_DIRECTORY . DIRECTORY_SEPARATOR . 'favicon.ico';
                    $this->sendResourceFile();
                    exit;
                }
                $this->sendErrorHeaders();
                $this->sendErrorDocument();
                exit;
            } else if ($this->_bResourceProtected && !isset($this->_viewContents)) {
                Logger::log(LogLevel::INFO, "Resource Leach Blocked from: ".$_SERVER['REMOTE_ADDR']);
                $this->sendAntiLeachResponse();
                exit;
            } else if (!isset($this->_viewContents)) {
                $this->sendErrorHeaders();
                $this->sendErrorDocument();
                exit;
            }
        }

        // Parse out the user agent
        $this->userAgentParser();

        // Check our cookie policy requirements - displaying an optin page if necessary
        $this->cookiePolicy();

        // If we don't have a template to use then locate one
        if (!isset($this->_templateFileName)) {
            $this->_templateFileName = $this->findReqTemplate();
        }

        // load the template output and store it
        $this->loadTemplate($this->_templateFileName);

        // main execution of our controllers
        $this->executeControllers();

        // Display the view
        $this->display();
    }


    /**
     * Checks to see if the user has opted in to our preferred use of cookies
     * There are 4 types of policy:
     *  - quiet = permit default session handling without any user optin
     *  - disabled = block sessions and cookies completely within the framework
     *  - internal = show the SAL optin page to permit use of cookies and sessions
     *  - [customfile] = use an optin page in the Site's template path
     */
    private function checkSessionPolicy()
    {
        if (isset($this->_oSiteConfig->sessionPolicy)) {
            $this->_sessionPolicy = strtolower(trim($this->_oSiteConfig->sessionPolicy));
        } else {
            $this->_sessionPolicy = 'quiet';
        }
        switch ($this->_sessionPolicy)
        {
            case 'quiet':
                $this->_bCookiesOK = true;
                $this->_bSessionsOK = true;
                break;

            case 'internal':
                $this->_bCookiesOK = false;
                $this->_bSessionsOK = true;
                break;

            case 'disabled':
                $this->_bCookiesOK = false;
                $this->_bSessionsOK = false;
                break;

            default:
                $this->_bSessionsOK = true;
                $this->_bCookiesOK = false;
        }

        if ($this->_sessionPolicy == 'disabled') {
            Logger::log(LogLevel::DEBUG, "Cookie Policy disabled");
            return;
        } else if ($this->_sessionPolicy == 'quiet') {
            $this->setOptInCookie(1);
            return;
        }

        // Have cookies been accepted by the user
        if (isset($_COOKIE[$this->_optInCookieName])) {
            $this->_bCookiesOK = true;
            Logger::log(LogLevel::DEBUG, "Use of cookies permitted");
            ini_set('session.use_cookies', '1');
        } else {
            // Start our session handling using file based sessions
            Logger::log(LogLevel::DEBUG, "Using file based sessions");
            ini_set('session.use_cookies','0');
            // Are we using automatic URL rewriting?
            $this->_bSessionUrlRewriting = ini_get('session.use_trans_sid') == '1' ? false : true;
        }
    }


    /**
     * Initialise our session handling - either using cookie based sessions or file based sessions with URL rewriting
     */
    private function initSessionHandling()
    {
        if ($this->_bSessionsOK) {
            if (!$this->_bCookiesOK) {
                // Get any existing sessionID from the querystring
                if ($this->_oRequest->getQueryString()) {
                    parse_str($this->_oRequest->getQueryString(), $arr);
                    if (isset($arr['PHPSESSID'])) {
                        $this->_sessionID = $arr['PHPSESSID'];
                        session_id($arr['PHPSESSID']);
                    }
                } else if (isset($_GET['PHPSESSID'])) {
                    $this->_sessionID = $_GET['PHPSESSID'];
                    session_id($_GET['PHPSESSID']);
                }
            }

            // Start our session handling
            session_start();
            if ($this->_bCookiesOK == false && !isset($this->_sessionID)) {
                $this->_sessionID = session_id();
            }

            Logger::debug("SessionID = ".$this->_sessionID);
        }
    }


    /**
     * Show any cookie opt-in page
     * @throws Exception
     */
    private function cookiePolicy()
    {
        // if the user has already rejected the use of cookies then skip this
        if ((isset($_SESSION['cookies']) && $_SESSION['cookies'] == 'rejected') || ($this->_bSessionsOK && $this->_bCookiesOK)) return;

        // if the policy has disabled cookies then we're done here
        if ($this->_sessionPolicy == 'disabled') return;

        // if the user agent appears to be a search engine bot then skip any cookie policy
        if ($this->_aUserAgentInfo['typ'] == 'Robot') return;

        // Do we need to show a cookie opt in page
        if (!$this->_bCookiesOK && $this->findReqTemplate()) {

            if ($this->_oRequest->getQueryString()) {
                parse_str($this->_oRequest->getQueryString(), $arr);
                if (isset($arr['cookies'])) {
                    $cookie = $arr['cookies'];
                }
            } else if (isset($_GET['cookies'])) {
                $cookie = $_GET['cookies'];
            }

            if ($cookie == 'accept') {
                $this->setOptInCookie();
                Logger::debug("Use of cookies accepted. Sending browser to their original request");
                if ($this->_bSessionsOK && isset($_SESSION['origRequest'])) {
                    header("Location: " . $_SESSION['origRequest']);
                } else {
                    header("Location: /");
                }
                exit;
            } else if ($cookie == 'reject') {
                $_SESSION['cookies'] = 'rejected';
            } else if ($this->_sessionPolicy == 'internal') {
                $optinPage = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/_sys/html_cookieOptin.tpl';
                // We need to display mobile friendly pages for the internal optin page
                if (!is_a($this->_oDevice, '\Mobile_Detect')) {
                    $this->mobileDetect(true);
                }
            } else if ($this->_sessionPolicy != 'disabled' && !$this->_bCookiesOK) {
                $optinPage = $this->_templatePath . DIRECTORY_SEPARATOR . $this->_sessionPolicy;
            }

            if (isset($optinPage)) {
                Logger::debug("Showing cookie policy opt-in page: $optinPage");
                if (file_exists($optinPage) && is_readable($optinPage)) {
                    // What page did they want (eg index.html or page2 or myfolder/index)
                    if ($this->_bSessionsOK && isset($_SERVER['REDIRECT_URL'])) {
                        $_SESSION['origRequest'] = trim($_SERVER['REDIRECT_URL']);
                    } else if ($this->_bSessionsOK && isset($_SERVER['SCRIPT_NAME'])) {
                        $_SESSION['origRequest'] = trim($_SERVER['SCRIPT_NAME']);
                    }

                    $this->_templateFileName = $optinPage;
                } else {
                    throw new ProjectException('Optin page missing or unreadable: ' . $optinPage);
                }
            }

        }
    }


    /**
     * Saves the current timestamp in the optInCookie so we know the web visitor has opted in to cookies
     */
    private function setOptInCookie($numDays = 90)
    {
        if (!is_int($numDays) && is_numeric($numDays)) {
            $numDays = intval($numDays);
        } else if (!is_int($numDays)) {
            $numDays = 90;
        }
        if (isset($_COOKIE[$this->_optInCookieName])) {
            $cookieVal = $_COOKIE[$this->_optInCookieName];
            if (is_int($cookieVal)) {
                setcookie($this->_optInCookieName, $cookieVal, Project::isDev() ? 0 : time()+(60*60*24*$numDays), '/', isset($this->_optInCookieDomain) ? $this->_optInCookieDomain : '');
                return;
            }
        }
        setcookie($this->_optInCookieName, date('U'), Project::isDev() ? 0 : time()+(60*60*24*90), '/', isset($this->_optInCookieDomain) ? $this->_optInCookieDomain : '');
        Logger::debug("Cookie Policy Opt-in saved to cookie");
    }


    /**
     * Load up information about the remote device (is it a mobile or a tablet, etc)
     * @see http://code.google.com/p/php-mobile-detect/wiki/Mobile_Detect
     * @param $force bool override the Config settings
     */
    private function mobileDetect($force = false)
    {
        if ($force || (isset($this->_oSiteConfig->attributes()->mobileDetect) && $this->_oSiteConfig->attributes()->mobileDetect == 1)) {
            if ($this->_bSessionsOK) {
                if ($_SESSION['device']) {
                    $this->_oDevice = unserialize($_SESSION['device']);
                    if (is_a($this->_oDevice, '\Mobile_Detect')) {
                        Logger::debug("Device info taken from session storage");
                        return;
                    } else {
                        Logger::debug("Session claims to have device info, but failed to extract it");
                    }
                }
            }
            $this->_oDevice = new \Mobile_Detect;
            Logger::debug("Device info gleaned");
            if ($this->_bSessionsOK) {
                $_SESSION['device'] = serialize($this->_oDevice);
            }
        }
    }


    /**
     * Use the vendor supplied UASparser to examine the browser user agent and create a useful
     * associative array of info about it
     * First time this is run it will download a cache of the UA info so will take a few seconds
     * @todo use APC to stop concurrent web downloads on a busy site
     */
    private function userAgentParser()
    {
        if (!isset($this->_tempFolderPath)) {
            $this->initTempFolder($this->_oWebConfig->cachePath);
        }
        if (isset($this->_tempFolderPath)) {
            /**
             * @var $parser \UASparser
             */
            try {
                $parser = new \UASparser();
                // Does the Config XML (project.xml) define the skipInternet option
                $attr = array();
                if (isset($this->_oSiteConfig->UASparser)) {
                    $attr = iterator_to_array($this->_oSiteConfig->UASparser->attributes());
                }
                // Do we enable fetching the latest UA profiles from the internet
                if (isset($attr['skipInternet'])) {
                    $parser->skipInternet();
                } else if (isset($this->_tempFolderPath)) {
                    $parser->SetCacheDir($this->_tempFolderPath . DIRECTORY_SEPARATOR . 'uas');
                } else {
                    $parser->skipInternet();
                }

                \NDN\SAL\Debug::log("Parsing user-agent", \NDN\SAL\Debug::DEBUG);

                // Gets information about the current browser's user agent
                $aUAdetails = $parser->Parse();
            }
            catch (SalException $ex)
            {
                \NDN\SAL\Debug::log($ex->getMessage(), \NDN\SAL\Debug::WARN);
            }
            catch (\Exception $ex)
            {
                throw $ex;
            }

            if (!isset($aUAdetails)) {
                \NDN\SAL\Debug::log("Unknown user-agent", \NDN\SAL\Debug::DEBUG);
                $aUAdetails = array();
                $aUAdetails['typ'] = 'unknown';
                $aUAdetails['ua_family'] = 'unknown';
                $aUAdetails['ua_name'] = 'unknown';
                $aUAdetails['ua_company'] = 'unknown';
                $aUAdetails['ua_company_url'] = 'unknown';
                $aUAdetails['ua_url'] = 'unknown';
                $aUAdetails['ua_info_url'] = 'unknown';
                $aUAdetails['ua_icon'] = 'unknown';
            } else if ($aUAdetails['typ'] == 'unknown' && preg_match('/googlebot|^$/i', $_SERVER['HTTP_USER_AGENT'])) {
                \NDN\SAL\Debug::log("User-agent identified as googlebot", \NDN\SAL\Debug::DEBUG);
                $aUAdetails['typ'] = 'Robot';
                $aUAdetails['ua_family'] = 'Googlebot';
                $aUAdetails['ua_name'] = 'Googlebot/2.1';
                $aUAdetails['ua_company'] = 'Google Inc.';
                $aUAdetails['ua_company_url'] = 'http://www.google.com';
                $aUAdetails['ua_url'] = 'http://support.google.com/webmasters/bin/answer.py?hl=en&answer=182072';
                $aUAdetails['ua_info_url'] = 'http://user-agent-string.info/list-of-ua/bot-detail?bot=Googlebot';
                $aUAdetails['ua_icon'] = 'bot_googlebot.png';
            } else {
                \NDN\SAL\Debug::log("User-agent identified as ".$aUAdetails['ua_family'], \NDN\SAL\Debug::DEBUG);
            }

            $this->_aUserAgentInfo = $aUAdetails;
        }
    }


    /**
     * Checks if a resource file has been requested
     * Basically, any request that isn't for a handled extension (eg htm, html, php)
     * is assumed to be for a resource
     * Returns true and sets $this->bResource to true
     *
     * @param $request string
     * @return bool true if resource requested
     */
    private function isResourceRequest($request)
    {
        // replace backward slash with DIRECTORY_SEPARATOR
        $request = str_replace('\\', DIRECTORY_SEPARATOR, $request);

        $aResourceAlias = $this->isResourceAliasRequest($request);
        if (is_array($aResourceAlias)) {
            // the resource is within a ResourceAlias path defined in the project xml
            $this->_bResource = true;
            $pos = 0;
            for ($i=0; $i<strlen($request); $i++)
            {
                if (substr($request, $i, strlen($aResourceAlias['alias'])) == $aResourceAlias['alias']) {
                    $pos = $i + strlen($aResourceAlias['alias']) + 1;
                    break;
                }
            }
            $request = substr($request, $pos);
            $this->_sResourceFilename = $aResourceAlias['path'] . DIRECTORY_SEPARATOR . $request;
            if ($aResourceAlias['protected']) $this->_bResourceProtected = true;
            Logger::debug("Request identified as a resource request");
        } else {
            // Assume a resource file if the file extension doesn't match one of our handled extensions
            $extension = $this->parseFileExtensionFromUrl($request);

            if ($extension && !in_array($extension, $this->_aHandledExtensions)) {
                $this->_bResource = true;
                if ($this->_bSessionsOK && isset($_SESSION['sal_templatePath'])) {
                    $srchFileName = $_SESSION['sal_templatePath'] . $request;
                    Logger::debug("Looking for resource at: $srchFileName");
                    if (file_exists($srchFileName) && is_readable($srchFileName)) {
                        $this->_sResourceFilename = $srchFileName;
                        Logger::debug("Request identified as a resource request");
                    }
                }
                if (isset($this->_templatePath)) {
                    $srchFileName = $this->_templatePath . $request;
                    Logger::debug("Looking for resource at: $srchFileName");
                    if (file_exists($srchFileName) && is_readable($srchFileName)) {
                        $this->_sResourceFilename = $srchFileName;
                        Logger::debug("Request identified as a resource request");
                    }
                }
            }
        }

    }


    /**
     * Extracts the core file extension (eg html) from the URL request stub
     *
     * @param $requestDoc
     * @return bool|string
     */
    private function parseFileExtensionFromUrl($requestDoc)
    {
        $extension = false;
        if (strrpos($requestDoc, '.')) {
            $extension = substr($requestDoc, strrpos($requestDoc, '.') + 1);
        }
        // remove any unwanted chars at the end
        $arr = preg_split('/[^0-9a-zA-Z]/', $extension, 2);
        if (count($arr) > 0) {
            // false positive - this is a controller request with an extension in the middle somewhere
            $extension = $arr[0];
        }

        return $extension;
    }


    /**
     * If the request is stubbed to a Resource alias path then return the resource alias array stub
     * Resource paths are defined in the project xml and ensure that any files within are
     * served raw - ie, they don't go through Smarty
     * Useful for javascript files which can contain curly brackets and trigger smarty
     *
     * @param $request string
     * @return array
     */
    private function isResourceAliasRequest($request)
    {
        foreach ($this->_aResourcePaths AS $alias => $aResource) {
            if (substr($request, 0, strlen($alias)) == $alias) {
                $arr['alias'] = $alias;
                foreach ($aResource AS $key => $val) {
                    $arr[$key] = $val;
                }
                Logger::debug("Request is for a resource alias");
                return $arr;
            }
        }
    }


    /**
     * Sends our Anti-Leach response to the client
     */
    private function sendAntiLeachResponse()
    {
        Logger::debug('Leach request for protected resource: ' . $this->_oRequest->getDocument());
        $this->sendErrorHeaders();
        $this->sendErrorDocument();
    }


    /**
     * Choose the right Content-Type header for the document
     * Uses the $this->_viewDocType to choose
     *
     * @param string $file absolute filename of the file (optional)
     */
    private function setDocTypeHeaders($file = false)
    {
        if (isset($this->_viewDocType)) {
            $docType = $this->_viewDocType;
        } else {
            $docType = 'html';
        }

        // Determine Content Type
        switch ($docType) {
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
            default:
                if ($file) {
                    $file = escapeshellarg($file);
                    $ctype = shell_exec('file -bi ' . $file);
                }
                break;
        }

        if ($ctype) {
            Logger::debug("HTTP Content-Type chosen as: ".$ctype);
            $this->_aHttp200Headers['Content-Type'] = $ctype;
        }
    }

    /**
     * Send the resource file to the browser
     *
     * @return mixed
     * @throws SalException
     */
    private function sendResourceFile()
    {
        if (!isset($this->_sResourceFilename)) {
            throw new SalException('Invalid call to Web_Handler::sendResourceFile for: '.$this->_oRequest->getDocument());
            return;
        }
        // parse the name of the file
        $path_parts = pathinfo($this->_sResourceFilename);
        $this->_viewDocType = strtolower($path_parts['extension']);
        $this->setDocTypeHeaders($this->_sResourceFilename);
        $filesize = filesize($this->_sResourceFilename);

        // set our other headers for the resource
        $this->_aHttp200Headers['Content-Length'] = $filesize;
        $this->_aHttp200Headers['Last-Modified'] = date('r', filectime($this->_sResourceFilename));

        if (!Project::isDev()) {
            // production servers - resources expire in 1 month
            $this->_aHttp200Headers['Expires'] = date('r', strtotime('+1 month'));
            $this->_aHttp200Headers['Cache-Control'] =  'public, max-age=2592000, must-revalidate';
            $this->_aHttp200Headers['Pragma'] = 'public';
            $this->_aHttp200Headers['ETag'] = md5(filectime($this->_sResourceFilename));
        }
        Logger::debug("Sending resource file to browser");
        $this->sendSuccessHeaders();
        ob_clean();
        flush();
        readfile($this->_sResourceFilename);
    }


    /**
     * sends a standard error headers to the browser client
     */
    private function sendErrorHeaders()
    {
        if (!headers_sent()) {
            if (\NDN\SAL\Config::about('url')) header('X-Powered-By: '. (string)\NDN\SAL\Config::about('url'));
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Status: 404 Not Found');
        } else {
            Logger::debug('Unable to send error headers (headers_sent() == true)');
        }
    }


    /**
     * delivers our error document to the browser
     * @param $message string override the main message
     */
    private function sendErrorDocument($message = false)
    {
        if (isset($this->_errorDocFile)) {
            if (isset($this->_errorDocPath)) {
                $path = $this->checkProjectDir($this->_errorDocPath);
                if ($path) $this->_templatePath = $path;
            }

            $errDoc = $path . DIRECTORY_SEPARATOR . $this->_errorDocFile;
            if (file_exists($errDoc) && is_readable($errDoc)) {
                $this->display($this->_errorDocFile);
                return;
            }
        }

        Logger::debug("Sending error document");

        $this->sendErrorHeaders();
        if (is_string($message)) {
            $format = '<!DOCTYPE HTML PUBLIC \" -//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>'.$message.'</p><hr><address>%4$s (<a href="http://%5$s" target="_blank">%5$s</a>) running on %2$s port %3$s</address></body></html>';
        } else if (!$this->_bResource) {
            $format = '<!DOCTYPE HTML PUBLIC \" -//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL <em>%1$s</em> was not found on this server at %6$s.</p><hr><address>%4$s (<a href="http://%5$s" target="_blank">%5$s</a>) running on %2$s port %3$s</address></body></html>';
        } else {
            $format = '<!DOCTYPE HTML PUBLIC \" -//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested resource <em>%1$s</em> was not found on this server.</p><hr><address>%4$s (<a href="http://%5$s" target="_blank">%5$s</a>) running on %2$s port %3$s</address></body></html>';
        }
        printf($format, $this->_oRequest->getDocument(), substr($_SERVER['SERVER_SOFTWARE'], 0, strpos($_SERVER['SERVER_SOFTWARE'], ' ')), $_SERVER["SERVER_PORT"], (string)\NDN\SAL\Config::about('name'), (string)\NDN\SAL\Config::about('url'), $this->_templatePath);
    }


    /**
     * sends the no-cache headers (and any other default headers with a 200 response) to the browser
     */
    private function sendSuccessHeaders()
    {
        if (!headers_sent()) {
            Logger::debug("Sending 200 response and other HTTP headers");
            // 200 OK response
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
            $aHeaders['Last-Modified'] = date('r', filectime($this->_templateFileName));
            $aHeaders['ETag'] = md5(filectime($this->_templateFileName));
//            $aHeaders['Content-Type'] = 'text/html; charset=utf-8';
            $aHeaders['X-Powered-By'] = \NDN\SAL\Config::about('url');

            // Replace/add any custom headers from any controllers
            $aHeaders = array_replace($aHeaders, $this->_aHttp200Headers);

            // now, finally, we send the headers
            foreach ($aHeaders AS $name => $value) {
                if ($value === false) continue;
                $hdr = sprintf('%s: %s', $name, $value);
                header($hdr);
            }
            if (!isset($aHeaders['Content-Length']) && isset($this->_viewContents) && is_string($this->_viewContents)) {
                header('Content-Length: ' . strlen($this->_viewContents));
            }

        } else {
            Logger::debug('Unable to send headers (already sent)');
        }
    }


    /**
     * Initialises the smarty environment
     * which means creating lots of folders (if they don't already exist)
     * @return \Smarty
     */
    private function initSmarty()
    {
        Logger::debug("Instantiating Smarty object");

        $oSmarty = new \Smarty();
        $oSmarty->muteExpectedErrors();

        // Set the template path (where the HTML templates reside)
        $oSmarty->setTemplateDir($this->_templatePath);

        // Configure the plugins directories
        $this->initTemplatePluginDirs($oSmarty);

        // Configure our cache & temp directories
        $this->initSmartyCache($oSmarty);

        return $oSmarty;
    }


    /**
     * Locate the base directory where the web templates reside
     *
     * @return void
     */
    private function setTemplatePath()
    {
        if (isset($this->_oSiteConfig->path)) {
            // Check the template path - raise an Exception if any problems found
            $templatePath = $this->checkProjectDir($this->_oSiteConfig->path, true);
            $this->_templatePath = $templatePath;
        } else {
            Logger::warning("path missing from Config - defaulting to html");
            // Check the template path - raise an Exception if any problems found
            $templatePath = $this->checkProjectDir('html', true);
            $this->_templatePath = $templatePath;
        }
        Logger::debug("TemplatePath selected as: $templatePath");
    }


    /**
     * Tells the template engine where our plugin folders reside
     * @param \Smarty $oSmarty
     */
    private function initTemplatePluginDirs(\Smarty $oSmarty)
    {
        // Add any project defined smarty plugins directory
        foreach ($this->_oWebConfig->pluginPath AS $pluginDir) {
            $pluginDir = $this->checkProjectDir($pluginDir);
            if ($pluginDir) {
                $oSmarty->addPluginsDir($pluginDir);
            }
        }

        // Add the SAL platform plugins
        $platformPluginDir = $this->checkProjectDir($this->_platformPath . DIRECTORY_SEPARATOR . 'plugins');
        if ($platformPluginDir) {
            $oSmarty->addPluginsDir($platformPluginDir);
        }
    }




    /**+
     * Prepares the cache folder for Smarty
     * @param \Smarty $oSmarty
     */
    private function initSmartyCache(\Smarty $oSmarty)
    {

        // If the project temp folder has not been initialised, do it now
        if (!isset($this->_tempFolderPath)) {
            $this->initTempFolder($this->_oWebConfig->cachePath);
        }
        // Create a sub-folder in the temp folder for Smarty
        if (isset($this->_tempFolderPath)) {
            $documentRoot = $this->initProjectDir($this->_tempFolderPath . DIRECTORY_SEPARATOR . 'Smarty');
        }
        // If we still don't have a documentRoot then we can't continue
        if (!$documentRoot) {
            throw new ProjectException('Unable to create a working temp directory');
        }

        // create a template cache
        if (isset($this->_oSiteConfig->alias)) {
            $documentRoot = $this->initProjectDir($documentRoot . DIRECTORY_SEPARATOR . (string)$this->_oSiteConfig->alias);
        } else {
            throw new ProjectException('Missing &lt;alias&gt;&lt;/alias&gt; element from Web_Project block in project.xml');
        }

        // compiled templates dir
        $path = $documentRoot . DIRECTORY_SEPARATOR . 'templates_c' . DIRECTORY_SEPARATOR;
        if ($this->mkdir($path, false)) $oSmarty->setCompileDir($path);
        // cache dir
        $path = $documentRoot . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        if ($this->mkdir($path, false)) $oSmarty->setCacheDir($path);
        // configs dir
        $path = $documentRoot . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        if ($this->mkdir($path, false)) $oSmarty->setConfigDir($path);
    }


    /**
     * Returns the value of the config setting for the site
     *
     * @param $paramName string parameter name within the section
     * @param $arr bool do we want results returned as associative array, returning the section name that was matched as well as the result
     * @return string value of the parameter
     */
    function Site_Config_Value($paramName, $arr = false)
    {
        if (isset($this->_oSiteConfig->$paramName)) {
            if ($arr) {
                $aReturn = array('section' => (string)$this->_oSiteConfig->alias, 'result' => $this->_oSiteConfig->$paramName);
                return $aReturn;
            } else {
                return ($this->_oSiteConfig->$paramName);
            }
        }

        return false;
    }


    /**
     * Load the correct site config block from the main Config xml
     *
     * @return bool
     */
    private function loadConfig()
    {
        /**
         * @var $oBlock \SimpleXMLElement
         */
        $oBlock = \NDN\SAL\Config::getBlock('Web_Project');

        /**
         * @var $oWebBlock \SimpleXMLElement
         */
        $oWebBlock = simplexml_load_string($oBlock->asXML());

        if (is_a($oWebBlock, '\SimpleXMLElement')) {
            Logger::debug("Main Web Block of project XML loaded");
            $this->_oWebConfig = $oWebBlock;
            // Save the Web_Project block as it's own Config item for use later
            \NDN\SAL\Config::saveStaticData('ProjectBlock', $oWebBlock);

            /**
             * Can we load the config block from APC
             */
            if (Project::isDev() != true && ini_get('apc.enabled') == '1' && apc_exists('Config_'.$this->_oRequest->getDomain())) {
                $this->saveSiteConfig(simplexml_load_string(apc_fetch('Config_'.$this->_oRequest->getDomain())), false);
                return true;
            } else {
                $aHostnameBlock = $this->parseConfigBlockByHostname($oWebBlock);
            }

            /**
             * Now we iterate through the domain/hostname in our web request to
             * match against the most suitable SiteBlock hostname from above
             */
            $testString = $this->_oRequest->getDomain(); // start with the full requested hostname
            while (stristr($testString, '.')) {
                if (isset($aHostnameBlock[$testString])) {
                    $this->saveSiteConfig($aHostnameBlock[$testString]);
                    return true;
                } else if (isset($aHostnameBlock['*.'.$testString])) {
                    $this->saveSiteConfig($aHostnameBlock['*.'.$testString]);
                    return true;
                }
                // no match yet, so remove the first dotted portion of the requested hostname
                $testString = substr($testString, strpos($testString, '.') + 1); // www.mysite.com becomes mysite.com
            } // and loop

            // No match so far, try just '*' - our base
            if (isset($aHostnameBlock['*'])) {
                $this->saveSiteConfig($aHostnameBlock['*']);
                return true;
            }

            throw new ProjectException('No suitable Web_Project site in ' . \NDN\SAL\Config::getFileName());
        } else {
            throw new ProjectException('Missing Web_Project block in ' . \NDN\SAL\Config::getFileName());
        }
    }


    /**
     * @param \SimpleXMLElement $oBlock
     * @param bool $bCache enable or disable APC caching of results
     */
    private function saveSiteConfig(\SimpleXMLElement $oBlock, $bCache = true)
    {
        $this->_oSiteConfig = $oBlock;
        \NDN\SAL\Config::saveStaticData('SiteConfig', $oBlock);
        if ($bCache && ini_get('apc.enabled') == '1') {
            apc_store('Config_'.$this->_oRequest->getDomain(), $oBlock->asXML(), 1800);
        }
    }


    /**
     * Parses the Web_Project block from the project.xml returning it in an associative array by hostname
     *
     * @param \SimpleXMLElement $oWebProjectBlock
     * @return array
     */
    private function parseConfigBlockByHostname(\SimpleXMLElement $oWebProjectBlock)
    {
        $aHostname = array();

        /**
         * We now have the <Web_Project> block of XML from the project.xml
         * we're going to iterate through all <site> blocks of that
         * to extract all the hostnames/sites we support
         */
        /**
         * @var $oSiteBlock \SimpleXMLElement
         */
        foreach ($oWebProjectBlock->site AS $oSiteBlock)
        {
            foreach ($oSiteBlock->attributes() AS $attrName=>$attrValue)
            {
                if (strtolower($attrName) == 'hostname' || strtolower($attrName) == 'host') {
                    $hostname = (string)$attrValue;
                    $hostname = trim(strtolower($hostname));
                    if (!isset($aHostname[$hostname])) {
                        $aHostname[$hostname] = $oSiteBlock;
                    }
                }
            }
        }

        return $aHostname;
    }


    /**
     * Is the web user is requesting an internal SAL admin function?
     */
    private function checkForAdminResource()
    {
        if (isset($this->_oWebConfig->admin)) {
            /**
             * @var $admin \SimpleXMLElement
             */
            $admin = $this->_oWebConfig->admin;
            // Location of the SAL admin templates
            $testPath = $this->_platformPath . DIRECTORY_SEPARATOR . '_admin' . DIRECTORY_SEPARATOR . 'templates' ;

            // is the request within our project defined admin space
            if (isset($admin->uri) && substr($this->_oRequest->getDocument(), 0, strlen((string)$admin->uri)) ==  $admin->uri) {
                Logger::debug("Admin resource request");

                // rewrite the requested doc relative to the admin stub
                $relativeReq = str_replace((string)$admin->uri, '', $this->_oRequest->getDocument());
//                $this->_oRequest->getDocument() = $relativeReq; // @todo sort this

                // first we re-configure the template path to be the platform admin/templates folder
                if (file_exists($testPath) && is_dir($testPath) && is_readable($testPath)) {
                    $this->_templatePath = $testPath;
                    $this->_bSessionsOK = true;
                    $this->_bCookiesOK = true;
                    $this->initSessionHandling();
                }

                // look for a template to match the request
                $testFilename = $testPath . DIRECTORY_SEPARATOR . $this->cleanRequestForFile($relativeReq) . '.tpl';
                if (file_exists($testFilename)) {
                    $this->_templateFileName = $testFilename;
                }
            } else if (isset($admin->uri) && isset($_SERVER['HTTP_REFERER'])) {
                /**
                 * Admin resources (images, css, etc) can often pretend to be from the root path
                 * not from the admin path (eg / instead of /_admin/)
                 * So if the referer is in the admin space and if this is a resource request
                 * then look for the file in the admin template path
                 */
                $aRefererParts = parse_url($_SERVER['HTTP_REFERER']);
                $extension = $this->parseFileExtensionFromUrl($this->_oRequest->getDocument());
                if (isset($aRefererParts['path']) && substr($aRefererParts['path'], 0, strlen((string)$admin->uri)) == $admin->uri && $extension && !in_array($extension, $this->_aHandledExtensions)) {
                    // first we re-configure the template path to be the platform admin/templates folder
                    if (file_exists($testPath) && is_dir($testPath) && is_readable($testPath)) {
                        $this->_templatePath = $testPath;
                        $this->_bSessionsOK = true;
                        $this->_bCookiesOK = true;
                        $this->initSessionHandling();
                    }

                    // look for a template to match the request
                    $testFilename = $testPath . DIRECTORY_SEPARATOR . $this->cleanRequestForFile($this->_oRequest->getDocument()) . '.tpl';
                    if (file_exists($testFilename)) {
                        $this->_templateFileName = $testFilename;
                    }

                }
            }
        }
    }


    /**
     * Load any config defined resource aliases
     */
    private function loadResourcePaths()
    {
        /**
         * @var $resourcePath \SimpleXMLELement
         */
        foreach ($this->_oSiteConfig->resource AS $resourcePath) {
            $arr = array();
            foreach ($resourcePath->attributes() AS $key => $val) {
                if ($key == 'protected') {
                    if (strtolower($val) == 'yes') {
                        $arr['protected'] = true;
                    } else {
                        $arr['protected'] = false;
                    }
                    break;
                }
            }
            $path = $this->checkProjectDir($resourcePath->path);
            if ($path) {
                $arr['path'] = $path;
                $this->_aResourcePaths[(string)$resourcePath->alias] = $arr;
            }
        }

        // a system folder
        if (!isset($this->_aResourcePaths['/_sys'])) {
            $sysdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_sys';
            if (file_exists($sysdir) && is_dir($sysdir) && is_readable($sysdir)) {
                $this->_aResourcePaths['/_sys'] = array(
                    'path' => $sysdir,
                    'protected' => false
                );
            }
        }
    }


    /**
     * Assign the ErrorDoc
     * nothing is assigned if the file is not specified or does not exist
     *
     * @return void
     */
    private function setErrorDocFile()
    {
        /**
         * Do we have a valid error document
         */
        if (isset($this->_oSiteConfig->errorDoc)) {
            $oStub = $this->_oSiteConfig->errorDoc;
            if (is_a($oStub, 'SimpleXMLElement')) {
                if (isset($oStub->file)) $this->_errorDocFile = $oStub->file;
                if (isset($oStub->path)) $this->_errorDocPath = $oStub->path;
            } else if (is_string($oStub)) {
                $this->_errorDocFile = (string)$oStub;
            }
        }
    }


    /**
     * Finds any controllers that are to be included in our processing
     */
    private function findControllers()
    {
        $this->_aControllers = \NDN\SAL\Web_Controller::getControllers($this->_oSiteConfig);
    }


    /**
     * Iterates through any controllers and calls their prepare() method
     * This is a "pre-launch" opportunity for the controllers to change default behaviour
     */
    private function prepControllers()
    {
        /* @var $oController \NDN\SAL\ControllerAbstract */
        foreach ($this->_aControllers AS $oController) {
            if ($oController->bControllerActive) {
                $oldTag = \NDN\SAL\Debug::getTag();
                \NDN\SAL\Debug::setTag($oldTag . '_' . get_class($oController));
                $oController->setWebProject($this);
                $oController->initController();
                $oController->prepare();
                $this->_viewContents = $oController->getViewContents();
                $this->_viewDocType = $oController->getViewDocType();
                $this->_aTemplateVariables = $oController->getTemplateVariables();
                \NDN\SAL\Debug::setTag($oldTag);
            }
        }
    }


    /**
     * Prepares and calls any controller
     *
     * @return void
     */
    private function executeControllers()
    {
        /* @var $oController \NDN\SAL\ControllerAbstract */
        foreach ($this->_aControllers AS $oController) {
            if ($oController->bControllerActive) {
                $oldTag = \NDN\SAL\Debug::getTag();
                \NDN\SAL\Debug::setTag($oldTag . '_' . get_class($oController));
                $oController->setWebProject($this);
                $oController->initController();
                $oController->execute();
                $this->_viewContents = $oController->getViewContents();
                $this->_viewDocType = $oController->getViewDocType();
                $this->_aHttp200Headers = $oController->getHttp200Headers();
                \NDN\SAL\Debug::setTag($oldTag);
            }
            $oController = null;
        }
    }


    /**
     * Looks for a template that will fulfil the page request
     *
     * @return string
     */
    private function findReqTemplate()
    {
        // set our template name to the requested page if not already set
        $requestDoc = $this->_oRequest->getDocument();
        // clean the request to the template stub (eg index.html becomes index)
        $requestDoc = $this->cleanRequestForFile($requestDoc);

        // find the template's absolute filename
        $templateFileName = $this->findTemplateFilename($requestDoc);

        // if no template file found then look for a directory, eg templateName/index.html
        if (!$templateFileName) {
            if (substr($requestDoc, strlen($requestDoc)-1) != '/') {
                $requestDoc .= '/';
            }
            $templateFileName = $this->findTemplateFilename($requestDoc . 'index');
        }

        if (file_exists($templateFileName) && is_readable($templateFileName)) {
            return $templateFileName;
        }
    }


    /**
     * @param $requestDoc original request
     * @return string the cleaned template stub name
     */
    private function cleanRequestForFile($requestDoc)
    {
        // remove any leading slash /
        if (strlen($requestDoc) > 1 && substr($requestDoc, 0, 1) == '/') {
            $requestDoc = substr($requestDoc, 1);
        } else if ($requestDoc = '/') {
            $requestDoc = 'index.html';
        }

        // remove any extension
        $arr = explode('.', $requestDoc);
        if (count($arr) > 1) {
            unset($arr[count($arr) - 1]);
            $requestDoc = implode('.', $arr);
        }
        return $requestDoc;
    }

    /**
     * Looks for our template file and stores it in the _viewContents private getStaticData member
     *
     * @param string $templateFileName absolute filename of the template file
     * @return void
     */
    private function loadTemplate($templateFileName)
    {
        if ($templateFileName && file_exists($templateFileName) && is_readable($templateFileName)) {
            Logger::info("Loading template: $templateFileName");
            // prepare Smarty
            $oSmarty = $this->initSmarty();
            $this->assignTemplateVariables($oSmarty);
            $this->_viewContents = $oSmarty->fetch($templateFileName);
            if ($this->_bSessionUrlRewriting) {
                Logger::notice("Rewriting URLs in template: " . $templateFileName);
                $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
                $this->_viewContents = preg_replace_callback("/$regexp/siU", '\NDN\SAL\Web\Project::rewriteUrls', $this->_viewContents);
                $regexp = "<form\s[^>]*action=(\"??)([^\" >]*?)\\1[^>]*>";
                $this->_viewContents = preg_replace_callback("/$regexp/siU", '\NDN\SAL\Web\Project::rewriteUrls', $this->_viewContents);
            }
            $oSmarty = null;
            unset($oSmarty);
        }
    }


    /**
     * @param $outputHtml
     * @return mixed
     */
    private function rewriteUrls($outputHtml)
    {
        if (isset($outputHtml[2]) && !stristr($outputHtml[0], 'http://')) {
            $url = $outputHtml[2];
            if (strstr($url, '?')) {
                list($path, $qryString) = explode('?', $url, 2);
                parse_str($qryString, $arr);
                if (is_array($arr)) {
                    $arr['PHPSESSID'] = $this->_sessionID;
                    $qryString = http_build_query($arr);
                } else {
                    $arr .= '&PHPSESSID=' . $this->_sessionID;
                }
                $url = $path . '?' . $qryString;
            } else {
                $url .= '?PHPSESSID=' . $this->_sessionID;
            }


            $return = str_replace($outputHtml[2], $url, $outputHtml[0]);
            return $return;
        }
        return $outputHtml[0];
    }


    /**
     * Tests for existence of a template file with the base name
     * eg "index" will look for index.tpl (extensions are from the WebConfig)
     *
     * @param string $stubTemplateName
     * @return string returns the full, absolute template filename - eg /path/to/project/templates/index.tpl or false if not found
     */
    private function findTemplateFilename($stubTemplateName)
    {
        foreach ($this->_oWebConfig->templateExtension AS $extension) {
            $testFileName = $stubTemplateName . '.' . $extension;
            if (file_exists($this->_templatePath . '/' . $testFileName) && is_readable($this->_templatePath . '/' . $testFileName)) {
                return $this->_templatePath . '/' . $testFileName;
            }
        }

        return false;
    }


    /**
     * Final step before displaying the template
     */
    private function assignTemplateVariables(\Smarty $oSmarty)
    {
        $oSmarty->assign('php_self', $this->_oRequest->getDocument());
        $oSmarty->assign('phpself', $this->_oRequest->getDocument());
        $oSmarty->assign('templatepath', $this->_templatePath);
        if (isset($this->_templateFileName)) {
            $oSmarty->assign('webpath', dirname(str_replace($this->_templatePath, '', $this->_templateFileName)));
            $oSmarty->assign('templateFileName', $this->_templateFileName);
            $oSmarty->assign('pagename', substr(basename($this->_templateFileName), 0, strrpos(basename($this->_templateFileName), '.')));
        }
        if (isset($this->_oSiteConfig->name)) {
            $oSmarty->assign('sitename', (string)$this->_oSiteConfig->name);
        }
        $oSmarty->assign('useCookies', $this->_bCookiesOK);
        if (isset($this->_sessionID)) $oSmarty->assign('sessionID', $this->_sessionID);
        $oSmarty->assign('ssl', isset($_SERVER['HTTPS']) ? true : false);

        // add any site config variables
        /**
         * @var $item \SimpleXMLElement
         */
        foreach ($this->_oSiteConfig->param AS $item) {
            $paramName = (string) $item->attributes()->name;
            $paramValue = (string) $item;
            $oSmarty->assign($paramName, $paramValue);
        }

        // Is this a dev server
        if (Project::isDev()) {
            $oSmarty->assign('dev', true);
            $oSmarty->assign('isDev', true);
        }

        // Do we have a device object
        if (isset($this->_oDevice)) {
            $oSmarty->assign('device', $this->_oDevice);
        }

        // assign any variables that have come from the controllers
        if (is_array($this->_aTemplateVariables)) {
            foreach ($this->_aTemplateVariables AS $varName => $varValue) {
                $oSmarty->assign($varName, $varValue);
            }
        }
    }


    /**
     * displays the template to the browser
     *
     * @param $templateFileName string the template file to display
     */
    public function display()
    {
        if (isset($this->_viewContents) && is_string($this->_viewContents)) {
            if ($this->_bSessionsOK) {
                Logger::debug("Saving template path: ".$this->_templatePath);
                $_SESSION['sal_templatePath'] = $this->_templatePath;
            }
            if ($this->_bSessionsOK && !isset($_SESSION['clientAuth'])) {
                $arr = array(
                    'remote_addr' => $_SERVER['REMOTE_ADDR'],
                    'created'     => date('U')
                );
                $_SESSION['clientAuth'] = $arr;
            }
            Logger::info('HTTP 200 - '.$this->_oRequest->getMethod().': '.$this->_oRequest->getDocument());

            // set and send the HTTP headers
            $this->setDocTypeHeaders();
            $this->sendSuccessHeaders();

            // output the compiled template to the browser
            echo $this->_viewContents;
        } else {
            Logger::info('HTTP 404 - '.$this->_oRequest->getMethod().': '.$this->_oRequest->getDocument());
            $this->sendErrorHeaders();
            $this->sendErrorDocument();
        }
    }


    /**
     * @param $memberName
     * @return mixed
     */
    public function __get($memberName)
    {
        if (isset($this->$memberName) && !is_null($this->$memberName)) {
            return $this->$memberName;
        } else {
            $testname = '_'.$memberName;
            if (isset($this->$testname) && !is_null($this->$testname)) {
                return $this->$testname;
            }
        }
    }

}
