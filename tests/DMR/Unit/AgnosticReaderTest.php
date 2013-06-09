<?php

namespace DMR\Unit;

use DMR\Mapping\AgnosticReader;

/**
 * Reader unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class AgnosticReaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers DMR\Mapping\AgnosticReader::addManagerRegistry
	 * @covers DMR\Mapping\AgnosticReader::getRegistries
	 */
    public function testRegistriesPassedInTheConstructorAreAddedToTheList()
    {
    	$registryOR = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registryOD = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registries = array($registryOR, $registryOD);

    	$reader = new AgnosticReader($registries);
    
    	$this->assertSame($registries, $reader->getRegistries());
    }
    
    public function testDuplicatedRegistriesAreIgnored()
    {
    	$registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registries = array($registry, $registry, $registry, $registry);
    	$reader = new AgnosticReader($registries);
    
    	$this->assertCount(1, $reader->getRegistries());
    }
    
    public function testThrowsExceptionIfAInvalidValueIsPassedToTheConstructor()
    {
    	$this->setExpectedException('RuntimeException');

    	$registryOR = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registryOD = 'invalid';
    	$registries = array($registryOR, $registryOD);

    	$reader = new AgnosticReader($registries);
    }

    public function testReturnsTheFirstManagerFoundForAClass()
    {
    	$registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registry->expects($this->once())
    		->method('getManagerForClass')
    		->will($this->returnValue(true));
    	
    	$reader = new AgnosticReader();
    	$reader->addManagerRegistry($registry);
    	
    	$class = new \ReflectionClass($reader);
    	$method = $class->getMethod('getManagerForClass');
    	$method->setAccessible(true);
    	
    	$this->assertSame(true, $method->invokeArgs($reader, array('A')));
    }
    
    public function testThrowsAnExceptionIfAnyManagerIsFound()
    {
    	$this->setExpectedException('RuntimeException');

    	$registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    	$registry->expects($this->once())
	    	->method('getManagerForClass')
	    	->will($this->returnValue(false));

    	$reader = new AgnosticReader();
    	$reader->addManagerRegistry($registry);
    	 
    	$class = new \ReflectionClass($reader);
    	$method = $class->getMethod('getManagerForClass');
    	$method->setAccessible(true);
    	$method->invokeArgs($reader, array('A'));
    }
}
