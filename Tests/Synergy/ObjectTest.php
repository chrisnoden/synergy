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
namespace Synergy\Tests;

use Synergy\Object;

class ObjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Basic object instantiation
     */
    public function testObject()
    {
        $obj = new Object();
        $this->assertInstanceOf('Synergy\Object', $obj);
    }


    /**
     * Test the __toString() method
     */
    public function testObjectReturnValue()
    {
        $obj = new Object();
        $this->assertEquals('Synergy\Object', $obj->__toString());
    }

}