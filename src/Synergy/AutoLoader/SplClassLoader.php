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

namespace Synergy\AutoLoader;

use Synergy\Exception\SynergyException;

/**
 * SplClassLoader implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * @category Synergy\Project
 * @package  Synergy
 * @author   Chris Noden <chris.noden@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link     https://github.com/chrisnoden/synergy
 */
class SplClassLoader
{
    private $fileExtension = '.php';
    private $includePath;
    private $namespaceSeparator = '\\';


    /**
     * Creates a new <tt>SplClassLoader</tt> that loads classes of the
     * specified namespace.
     *
     * @param string $includePath directory where the classes are located
     */
    public function __construct($includePath)
    {
        $this->includePath = $includePath;
    }


    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     *
     * @param string $sep The separator to use.
     */
    public function setNamespaceSeparator($sep)
    {
        $this->namespaceSeparator = $sep;
    }


    /**
     * Gets the namespace seperator used by classes in the namespace of this class loader.
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }


    /**
     * Sets the base include path for all class files in the namespace of this class loader.
     *
     * @param string $includePath
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;
    }


    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }


    /**
     * Sets the file extension of class files in the namespace of this class loader.
     *
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }


    /**
     * Gets the file extension of class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }


    /**
     * Installs this class loader on the SPL autoload stack.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }


    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }


    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     *
     * @throws SynergyException
     * @return void
     */
    public function loadClass($className)
    {
        // Parse the requested className
        $aParts = explode($this->namespaceSeparator, $className);
        // Build our expected filename
        $fileName = $this->includePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $aParts) . $this->fileExtension;
        if (file_exists($fileName)) {
            if (!is_readable($fileName)) {
                throw new SynergyException(
                    sprintf(
                        'Error autoloading class %s - Unable to read file %s',
                        $className,
                        $fileName
                    )
                );
            }
            require_once($fileName);

            if (!class_exists($className)) {
                throw new SynergyException(
                    sprintf(
                        'File %s does not contain class declaration for %s',
                        $fileName,
                        $className
                    )
                );
            }
        }
    }

}
