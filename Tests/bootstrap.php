<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */


require_once('PHPUnit/Framework/TestCase.php');
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR .'autoload.php')) {
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR .'autoload.php');
} else {
    die("Not part of composer install");
}



