<?php

namespace DMR\Unit\Driver;

/**
 * File driver unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReadTheMappingDataUsingTheDefaultDriverWhenAvailable()
    {
        $className = 'Foo/Bar';
        $return = 'foo';

        $originalDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\FileDriver')
            ->setMethods(array('getElement'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $originalDriver->expects($this->once())
            ->method('getElement')
            ->with($className)
            ->will($this->returnValue($return))
        ;

        $mock = $this->getMockForAbstractClass('DMR\Mapping\Driver\File');
        $mock->setOriginalDriver($originalDriver);

        $this->assertSame($originalDriver, $mock->getOriginalDriver());
        $this->assertNull($mock->getLocator());
        $this->assertEquals($return, $mock->getMapping($className));
    }

    public function testShouldInvokeTheAbstractMethodIfNoDefaultDriverIsAvailable()
    {
        $className = 'Foo/Bar';
        $return = 'foo';

        $locator = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator')
            ->setMethods(array('findMappingFile'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $locator->expects($this->once())
            ->method('findMappingFile')
            ->with($className)
            ->will($this->returnValue($return))
        ;

        $mock = $this->getMockBuilder('DMR\Mapping\Driver\File')
            ->setMethods(array('loadMappingFile'))
            ->getMockForAbstractClass()
        ;

        $mock->expects($this->once())
            ->method('loadMappingFile')
            ->with($return)
            ->will($this->returnValue(array($className => $return)))
        ;

        $mock->setLocator($locator);

        $this->assertSame($locator, $mock->getLocator());
        $this->assertNull($mock->getOriginalDriver());
        $this->assertEquals($return, $mock->getMapping($className));
    }
}
