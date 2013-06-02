<?php

namespace DMR\Unit;

use DMR\Mapping\DriverFactory;
use DMR\Functional\FunctionalTestCase;

/**
 * DriverFactory unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDriverDoesNotExist()
    {
        $this->setExpectedException('RuntimeException');

        $orginalDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('TestXmlDriver')
            ->getMockForAbstractClass()
        ;

        $driver = DriverFactory::getDriver($orginalDriver, 'Invalid\Namescpace');
    }

    public function assertFileDriver($driverType)
    {
        $locator = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $orginalDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\FileDriver')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocator'))
            ->setMockClassName('Custom'.$driverType.'Driver')
            ->getMockForAbstractClass()
        ;

        $orginalDriver
            ->expects($this->once())
            ->method('getLocator')
            ->will($this->returnValue($locator))
        ;

        $driver = DriverFactory::getDriver($orginalDriver, FunctionalTestCase::DRIVER_NAMESPACE);
        $this->assertInstanceOf('DMR\Functional\Mock\Extension\Encoder\Mapping\Driver\\'.$driverType, $driver);
        $this->assertSame($orginalDriver, $driver->getOriginalDriver());
        $this->assertSame($locator, $driver->getLocator());
    }

    public function testXmlDriverLoader()
    {
        $this->assertFileDriver('Xml');
    }

    public function testYamlDriverLoader()
    {
        $this->assertFileDriver('Yaml');
    }

    public function testAnnotationDriverLoader()
    {
        $orginalDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('CustomAnnotationDriver')
            ->getMockForAbstractClass()
        ;

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $factory = $this->getMock('DMR\Mapping\DriverFactory', array('getAnnotationReader'));
        $factory::staticExpects($this->once())
            ->method('getAnnotationReader')
            ->will($this->returnValue($reader))
        ;

        $driver = $factory::getDriver($orginalDriver, FunctionalTestCase::DRIVER_NAMESPACE);
        $this->assertInstanceOf('DMR\Functional\Mock\Extension\Encoder\Mapping\Driver\Annotation', $driver);
        $this->assertSame($orginalDriver, $driver->getOriginalDriver());
        $this->assertSame($reader, $driver->getAnnotationReader());
    }

    public function testAnnotationFallback()
    {
        $orginalDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('CustomAbcDriver')
            ->getMockForAbstractClass()
        ;

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $factory = $this->getMock('DMR\Mapping\DriverFactory', array('getAnnotationReader'));
        $factory::staticExpects($this->once())
            ->method('getAnnotationReader')
            ->will($this->returnValue($reader))
        ;

        $driver = $factory::getDriver($orginalDriver, FunctionalTestCase::DRIVER_NAMESPACE);
        $this->assertInstanceOf('DMR\Functional\Mock\Extension\Encoder\Mapping\Driver\Annotation', $driver);
        $this->assertSame($orginalDriver, $driver->getOriginalDriver());
        $this->assertSame($reader, $driver->getAnnotationReader());
    }

    public function testChainDriverLoader()
    {
        $locator = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $xmlDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\FileDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('CustomXmlDriver')
            ->getMockForAbstractClass()
        ;

        $xmlDriver
            ->expects($this->once())
            ->method('getLocator')
            ->will($this->returnValue($locator))
        ;

        $yamlDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\FileDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('CustomYamlDriver')
            ->getMockForAbstractClass()
        ;

        $yamlDriver
            ->expects($this->once())
            ->method('getLocator')
            ->will($this->returnValue($locator))
        ;

        $annotationDriver = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->setMockClassName('CustomAnnotationDriver')
            ->getMockForAbstractClass()
        ;

        $driverList = array($xmlDriver, $yamlDriver, $annotationDriver);

        $chainDriver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain');
        $chainDriver->expects($this->once())
            ->method('getDrivers')
            ->will($this->returnValue($driverList))
        ;
        
        $chainDriver->expects($this->any())
	        ->method('getDefaultDriver')
	        ->will($this->returnValue($annotationDriver))
        ;

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $factory = $this->getMock('DMR\Mapping\DriverFactory', array('getAnnotationReader'));

        $factory::staticExpects($this->exactly(2))
            ->method('getAnnotationReader')
            ->will($this->returnValue($reader))
        ;

        $chain = $factory::getDriver($chainDriver, FunctionalTestCase::DRIVER_NAMESPACE);
        $this->assertInstanceOf('DMR\Mapping\Driver\Chain', $chain);

        $drivers = $chain->getDrivers();
        $this->assertCount(3, $drivers);
        $this->assertInstanceOf(FunctionalTestCase::DRIVER_NAMESPACE.'\Mapping\Driver\Xml', $drivers[0]);
        $this->assertInstanceOf(FunctionalTestCase::DRIVER_NAMESPACE.'\Mapping\Driver\Yaml', $drivers[1]);
        $this->assertInstanceOf(FunctionalTestCase::DRIVER_NAMESPACE.'\Mapping\Driver\Annotation', $drivers[2]);
        $this->assertSame($annotationDriver, $chain->getDefaultDriver()->getOriginalDriver());
    }
}
