<?php

namespace DMR\Functional;

/**
 * ODM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class AbstractReaderTest extends BaseTestCase
{
	abstract public function getReader();
	
	abstract public function getNamespaced($class);
	
    public function testShouldThrowsAnExceptionIfNoCustomDriversIsFound()
    {
    	$this->setExpectedException('RuntimeException');
    
    	$reader = $this->getReader();
    	$anyClass = $this->getNamespaced('Xml\User');
    	$reader->read($anyClass, 'Invalid/Namespace');
    }
    
    public function testShouldLoadTheMetadataForAnObjectMappedUsingXml()
    {
    	$reader = $this->getReader();
    	$metadata = $reader->read($this->getNamespaced('Xml\User'), self::DRIVER_NAMESPACE);
    
    	$this->assertEquals('name', $metadata['xml']['field']);
    	$this->assertEquals('sha1', $metadata['xml']['type']);
    	$this->assertEquals('xxx', $metadata['xml']['secret']);
    }
    
    public function testShouldLoadTheMetadataForAnObjectMappedUsingXmlWithInheritance()
    {
    	$reader = $this->getReader();
    	$metadata = $reader->read($this->getNamespaced('Xml\Child'), self::DRIVER_NAMESPACE);
    
    	$this->assertEquals('name', $metadata['xml']['field']);
    	$this->assertEquals('sha1', $metadata['xml']['type']);
    	$this->assertEquals('xxx', $metadata['xml']['secret']);
    }
    
    public function testShouldLoadTheMetadataForAnObjectMappedUsingYaml()
    {
    	$reader = $this->getReader();
    	$metadata = $reader->read($this->getNamespaced('Yaml\User'), self::DRIVER_NAMESPACE);
    
    	$this->assertEquals('name', $metadata['yaml']['field']);
    	$this->assertEquals('sha1', $metadata['yaml']['type']);
    	$this->assertEquals('xxx', $metadata['yaml']['secret']);
    }
    
    public function testShouldLoadTheMetadataForAnObjectMappedUsingAnnotations()
    {
    	$reader = $this->getReader();
    	$metadata = $reader->read($this->getNamespaced('Annotation\User'), self::DRIVER_NAMESPACE);
    
    	$this->assertEquals('name', $metadata['annotation']['field']);
    	$this->assertEquals('sha1', $metadata['annotation']['type']);
    	$this->assertEquals('xxx', $metadata['annotation']['secret']);
    }
}
