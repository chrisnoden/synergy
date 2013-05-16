<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
 */

if (!is_file($autoloadFile = __DIR__.'/../vendor/autoload.php')) {
    throw new \LogicException('Run "composer install --dev" to create autoloader.');
}

$loader = require $autoloadFile;
$loader->add('Synergy\Tests', __DIR__);
