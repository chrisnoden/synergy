<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

namespace Synergy;

/**
 * Synergy classes will typically extend this
 */
class Object {

    public function __construct() {}

    public function __destruct() {}

    /**
     * Object-to-string conversion.
     * Each class can override this method as necessary.
     *
     * @return string name of this class
     */
    public function __toString() {
        $class = get_class($this);
        return $class;
    }

}
