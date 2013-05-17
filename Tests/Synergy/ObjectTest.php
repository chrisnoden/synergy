<?php
/**
 * Created by Chris Noden using JetBrains PhpStorm.
 *
 * @author Chris Noden, @chrisnoden
 * @copyright (c) 2009 to 2013 Chris Noden
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