<?php

namespace DMR\Unit;

use DMR\Mapping\SimpleReader;

/**
 * Reader unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class SimpleReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testManagerIsAlwaysTheSamePassedInConstructor()
    {
    	$manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $reader = new SimpleReader($manager);
        
        $this->assertSame($manager, $reader->getManagerForClass('A'));
        $this->assertSame($manager, $reader->getManagerForClass('B'));
    }
}
