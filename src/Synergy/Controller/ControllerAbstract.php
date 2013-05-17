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

namespace Synergy\Controller;

use Synergy\Object;

/**
 * Class ControllerAbstract
 *
 * @package Synergy\Controller
 */
abstract class ControllerAbstract extends Object
{

    /**
     * @return void
     */
    abstract public function defaultAction();

}