<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy\Web;

use Synergy\Object;

class Router extends Object
{


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $document string the document/resource requested (URL)
     *
     * @return RootController
     */
    public function chooseController($document)
    {
        return new PageController();
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
            \NDN\SAL\Debug::log("Request identified as a resource request", \NDN\SAL\Debug::DEBUG);
        } else {
            // Assume a resource file if the file extension doesn't match one of our handled extensions
            $extension = $this->parseFileExtensionFromUrl($request);

            if ($extension && !in_array($extension, $this->_aHandledExtensions)) {
                $this->_bResource = true;
                if ($this->_bSessionsOK && isset($_SESSION['sal_templatePath'])) {
                    $srchFileName = $_SESSION['sal_templatePath'] . $request;
                    \NDN\SAL\Debug::log("Looking for resource at: $srchFileName", \NDN\SAL\Debug::DEBUG);
                    if (file_exists($srchFileName) && is_readable($srchFileName)) {
                        $this->_sResourceFilename = $srchFileName;
                        \NDN\SAL\Debug::log("Request identified as a resource request", \NDN\SAL\Debug::DEBUG);
                    }
                }
                if (isset($this->_templatePath)) {
                    $srchFileName = $this->_templatePath . $request;
                    \NDN\SAL\Debug::log("Looking for resource at: $srchFileName", \NDN\SAL\Debug::DEBUG);
                    if (file_exists($srchFileName) && is_readable($srchFileName)) {
                        $this->_sResourceFilename = $srchFileName;
                        \NDN\SAL\Debug::log("Request identified as a resource request", \NDN\SAL\Debug::DEBUG);
                    }
                }
            }
        }

    }


}
