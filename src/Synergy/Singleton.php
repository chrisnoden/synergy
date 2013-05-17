<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * PHP version 5
 *
 * @category  Project:Synergy
 * @package   Synergy
 * @author    Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 * @link      http://chrisnoden.com
 * @license   http://opensource.org/licenses/LGPL-3.0
 */

namespace Synergy;

/**
 * If you extend from this then you need a
 * protected static $instance
 * line
 */
abstract class Singleton
{
    protected static $instance = null;

    public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

    final private function __construct() {}

    final private function __clone() {}

}


