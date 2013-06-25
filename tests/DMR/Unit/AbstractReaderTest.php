<?php

namespace DMR\Unit;

use DMR\Mapping\AbstractReader;

/**
 * Reader unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class AbstractReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheIdIsUniquenessAndConsistent()
    {
        $a = AbstractReader::getCacheId('ClassA', 'Acme\A');
        $a2 = AbstractReader::getCacheId('ClassA', 'Acme\A');
        $ab = AbstractReader::getCacheId('ClassA', 'Acme\B');
        $b = AbstractReader::getCacheId('ClassB', 'Acme\B');
        $ba = AbstractReader::getCacheId('ClassB', 'Acme\A');

        $this->assertEquals($a, $a2);
        $this->assertNotEquals($a, $ab);
        $this->assertNotEquals($b, $ba);
        $this->assertNotEquals($a, $b);
        $this->assertNotEquals($ab, $ba);
    }

    public function testMappedSuperclassShouldReturnNull()
    {
    	$namespace = 'Acme';
        $className = $namespace . '\Class';

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = true;

        $reader = $this->getMockBuilder('DMR\Mapping\AbstractReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getManagerForClass'))
            ->getMockForAbstractClass()
        ;

        $factory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFor'))
            ->getMockForAbstractClass()
        ;

        $factory->expects($this->once())
            ->method('getMetadataFor')
            ->with($className)
            ->will($this->returnValue($metadata))
        ;

        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFactory'))
            ->getMockForAbstractClass()
        ;

        $manager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($factory))
        ;

        $reader->expects($this->once())
	        ->method('getManagerForClass')
	        ->with($className)
	        ->will($this->returnValue($manager))
        ;

        $this->assertNull($reader->read($className, $namespace));

    }

    public function testCachedDriverShouldGenerateDataWhenNoCacheIsAvailable()
    {
        $namespace = 'Acme';
        $className = $namespace.'/Class';
        $cacheId = uniqid($className);
        $return = array('cached' => 'data');

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = false;
        $metadata->name = $className;

        $reader = $this->getMockBuilder('DMR\Mapping\AbstractReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getCacheId', 'getManagerForClass'))
            ->getMockForAbstractClass()
        ;

        $reader::staticExpects($this->once())
            ->method('getCacheId')
            ->with($metadata->name, $namespace)
            ->will($this->returnValue($cacheId))
        ;

        $factory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFor', 'getCacheDriver'))
            ->getMockForAbstractClass()
        ;

        $factory->expects($this->once())
            ->method('getMetadataFor')
            ->with($className)
            ->will($this->returnValue($metadata))
        ;

        $cacheDriver = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $cacheDriver->expects($this->once())
            ->method('fetch')
            ->with($cacheId)
            ->will($this->returnValue($return))
        ;

        $factory->expects($this->once())
            ->method('getCacheDriver')
            ->will($this->returnValue($cacheDriver))
        ;

        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFactory'))
            ->getMockForAbstractClass()
        ;

        $manager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($factory))
        ;

        $reader->expects($this->once())
	        ->method('getManagerForClass')
	        ->with($className)
	        ->will($this->returnValue($manager))
        ;

		$data = $reader->read($className, $namespace);

        $this->assertSame($return, $data);
    }

    public function testCachedDriverShouldReturnCachedDataWhenAvailable()
    {
        $namespace = 'Acme';
        $className = $namespace.'/Class';
        $cacheId = uniqid($className);

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = false;
        $metadata->name = $className;
        $metadata->reflClass = null;

        $reader = $this->getMockBuilder('DMR\Mapping\AbstractReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getCacheId', 'getManagerForClass'))
            ->getMockForAbstractClass()
        ;

        $reader::staticExpects($this->once())
            ->method('getCacheId')
            ->with($metadata->name, $namespace)
            ->will($this->returnValue($cacheId))
        ;

        $factory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFor', 'getCacheDriver'))
            ->getMockForAbstractClass()
        ;

        $factory->expects($this->once())
            ->method('getMetadataFor')
            ->with($className)
            ->will($this->returnValue($metadata))
        ;

        $cacheDriver = $this->getMockForAbstractClass('Doctrine\Common\Cache\Cache');

        $cacheDriver->expects($this->once())
            ->method('fetch')
            ->with($cacheId)
            ->will($this->returnValue(false))
        ;

        $cacheDriver->expects($this->once())
            ->method('save')
            ->with($cacheId, array(), null)
        ;

        $factory->expects($this->once())
            ->method('getCacheDriver')
            ->will($this->returnValue($cacheDriver))
        ;

        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataFactory'))
            ->getMockForAbstractClass()
        ;

        $manager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($factory))
        ;

        $reader->expects($this->once())
	        ->method('getManagerForClass')
	        ->with($className)
	        ->will($this->returnValue($manager));

        $return = $reader->read($className, $namespace);
        $this->assertTrue(is_array($return));
        $this->assertEmpty($return);
    }
}
