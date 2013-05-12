<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 *
 * A collection of static tools that are useful when working with the platform
 */


namespace Synergy\Tools;


/**
 * seed the random number generator
 */
list($usec, $sec) = explode(" ", microtime());
srand((int)($usec*10));


class Tools
{

    /**
     * Generates a random string of length $length
     *
     * @static
     * @param $length
     * @param string $chars optional string of the characters that will be used to feed the output
     * @return string
     */
    public static function randomString($length, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
    {
        $size = strlen( $chars );
        $str = '';
        for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }

        return "$str";
    }


}




