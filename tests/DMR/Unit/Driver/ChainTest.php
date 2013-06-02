<?php

namespace DMR\Unit\Driver;

use DMR\Mapping\Driver\Chain;

/**
 * File driver unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class ChainTest extends \PHPUnit_Framework_TestCase
{
    public function testOriginalDriverDoNorhing()
    {
        $originalDriver = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $chain = new Chain();
        $chain->setOriginalDriver($originalDriver);
        $this->assertNull($chain->getOriginalDriver());
    }

    public function testAddDriver()
    {
        $driverA = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $driverB = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $driverC = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $chain = new Chain();
        $chain->addDriver($driverA, 'Acme/A');
        $chain->addDriver($driverB, 'Acme/B');
        $chain->addDriver($driverC, 'Acme/C');

        $drivers = $chain->getDrivers();
        $this->assertCount(3, $drivers);
        $this->assertContains($driverA, $drivers);
        $this->assertContains($driverB, $drivers);
        $this->assertContains($driverC, $drivers);
    }

    public function testRead()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->name = 'Acme/A/Class';

        $driverA = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $callback = function ($classMetadata, &$metadata) {
            $metadata['extension'] = true;
        };

        $driverA->expects($this->once())
            ->method('read')
            ->with($metadata)
            ->will($this->returnCallback($callback))
        ;

        $driverB = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $driverB->expects($this->never())->method('read');

        $chain = new Chain();
        $chain->addDriver($driverA, 'Acme/A');
        $chain->addDriver($driverB, 'Acme/B');

        $data = array();
        $chain->read($metadata, $data);
        $this->assertEquals(array('extension' => true), $data);
    }

    public function testReadWithDefaultDriver()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->name = 'Acme/A/Class';

        $callback = function ($classMetadata, &$metadata) {
            $metadata['extension'] = true;
        };

        $defaultDriver = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $defaultDriver->expects($this->once())
            ->method('read')
            ->with($metadata)
            ->will($this->returnCallback($callback))
        ;

        $chain = new Chain();
        $chain->setDefaultDriver($defaultDriver);

        $data = array();
        $chain->read($metadata, $data);

        $this->assertSame($defaultDriver, $chain->getDefaultDriver());
        $this->assertEquals(array('extension' => true), $data);
    }

    public function testReadNoDrivers()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $data = array();

        $chain = new Chain();
        $chain->read($metadata, $data);

        $this->assertNull($chain->getDefaultDriver());
        $this->assertEmpty($data);
    }
}
