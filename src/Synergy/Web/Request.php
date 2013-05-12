<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */


namespace Synergy\Web;


use Psr\Log\LogLevel;
use Synergy\Object;
use Synergy\Project;

class Request extends Object
{
    /**
     * @var Request
     */
    protected static $instance = null;
    /**
     * Is this a 403 or 404 error response
     *
     * @var string
     */
    private $_code;
    /**
     * the site that was requested (eg www.mysite.com or mysite.com)
     *
     * @var string
     */
    private $_domain;
    /**
     * the page that was requested
     * eg /index.html or /docs/page2.html or whatever
     *
     * @var string
     */
    private $_document;
    /**
     * Just the URI component of the request eg /page/index.html?param=yes
     * @var string
     */
    private $_uri;
    /**
     * query string
     *
     * @var string
     */
    private $_queryString;
    /**
     * The page request method - GET or POST
     *
     * @var string
     */
    private $_httpMethod;
    /**
     * Extension of the document requested (if any)
     *
     * @var string
     */
    private $_documentExtension;
    /**
     * The full URI that was requested
     *
     * @var string
     */
    private $_fullRequest;
    /**
     * The SERVER_PROTOCOL (eg HTTP/1.1)
     *
     * @var string
     */
    private $_protocol;


    public function __construct()
    {
        parent::__construct();
    }


    public function __destruct() {}


    /**
     * Populates the Request object data by examining the SERVER request
     *
     * @return Request
     */
    public static function build()
    {
        self::$instance = new Request();
        self::$instance->buildRequestData();
        return self::$instance;
    }


    /**
     * Extracts all the info we may need from the web server info
     * Populates the platform object getStaticData members
     */
    protected function buildRequestData()
    {
        Project::Logger()->log('Getting all the HTTP request data', LogLevel::DEBUG);

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

        // store the site that was requested (eg www.mysite.com)
        $this->_domain = $request->server->get('HTTP_HOST');

        // What page did they want (eg index.html or page2 or myfolder/index)
        if ($request->server->get('REDIRECT_URL')) {
            $this->_document = $request->server->get('REDIRECT_URL');
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $this->_document = $request->server->get('SCRIPT_NAME');
        }
        // replace forward slash with backward slash
        $this->_document = str_replace('\\', '/', $this->_document);
        // remove any multiple forward slashes (/// becomes /)
        $this->_document = preg_replace('#//+#', '/', $this->_document);
        // replace /index.php with just /
        if ($this->_document == '/index.php') {
            $this->_document = '/';
        }

        // Is there a query_string
        if ($request->server->get('REDIRECT_QUERY_STRING')) {
            $this->_queryString = $request->server->get('REDIRECT_QUERY_STRING');
        } else if ($request->server->get('QUERY_STRING')) {
            $this->_queryString = $_SERVER['QUERY_STRING'];
        }

        if ($request->server->get('REQUEST_URI')) {
            $this->_uri = $request->server->get('REQUEST_URI');
            $this->_fullRequest = $this->_domain . $this->_uri;
        } else {
            $this->_fullRequest = $this->_domain . $this->_document;
            if (isset($this->_queryString)) $this->_fullRequest .= '?' . $this->_queryString;
        }

        if ($request->server->get('SERVER_PROTOCOL')) {
            $this->_protocol = $request->server->get('SERVER_PROTOCOL');
        }

        // Is this a GET or a POST request
        if ($request->server->get('REDIRECT_REQUEST_METHOD')) {
            $this->_httpMethod = strtoupper($request->server->get('REDIRECT_REQUEST_METHOD'));
        } else if ($request->server->get('REQUEST_METHOD')) {
            $this->_httpMethod = strtoupper($request->server->get('REQUEST_METHOD'));
        }

        if ($this->_document != '/') {
            $this->_documentExtension = $this->parseFileExtensionFromUrl($this->_document);
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
     * @return string
     */
    public function getCode()
    {
        if (isset($this->_code)) return $this->_code;
    }


    /**
     * @return string
     */
    public function getDomain()
    {
        if (isset($this->_domain)) return $this->_domain;
    }


    /**
     * @return string
     */
    public function getDocument()
    {
        if (isset($this->_document)) return $this->_document;
    }


    /**
     * @return string
     */
    public function getQueryString()
    {
        if (isset($this->_queryString)) return $this->_queryString;
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        if (isset($this->_httpMethod)) return $this->_httpMethod;
    }


    /**
     * @return string
     */
    public function getFullRequest()
    {
        if (isset($this->_fullRequest)) return $this->_fullRequest;
    }


    /**
     * @return string
     */
    public function getUri()
    {
        if (isset($this->_uri)) return $this->_uri;
    }


    /**
     * @return string
     */
    public function getExtension()
    {
        if (isset($this->_documentExtension)) return $this->_documentExtension;
    }


    /**
     * @return string
     */
    public function getProtocol()
    {
        if (isset($this->_protocol)) return $this->_protocol;
    }

}
