<?php

namespace DMR\Unit;

use DMR\Mapping\Reader;

/**
 * Reader unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheIdUniquenessMapping()
    {
        $a = Reader::getCacheId('ClassA', 'Acme\A');
        $a2 = Reader::getCacheId('ClassA', 'Acme\A');
        $ab = Reader::getCacheId('ClassA', 'Acme\B');
        $b = Reader::getCacheId('ClassB', 'Acme\B');
        $ba = Reader::getCacheId('ClassB', 'Acme\A');

        $this->assertEquals($a, $a2);
        $this->assertNotEquals($a, $ab);
        $this->assertNotEquals($b, $ba);
        $this->assertNotEquals($a, $b);
        $this->assertNotEquals($ab, $ba);
    }

    public function testMappedSuperclass()
    {
        $className = 'Acme/Class';

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = true;

        $reader = $this->getMockBuilder('DMR\Mapping\Reader')
            ->disableOriginalConstructor()
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

        $reflection = new \ReflectionClass($reader);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);
        $property->setValue($reader, $manager);

        $this->assertNull($reader->read($className));

    }

    public function testCachedDriverNoCache()
    {
        $namespace = 'Acme';
        $className = $namespace.'/Class';
        $cacheId = uniqid($className);
        $return = array('cached' => 'data');

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = false;
        $metadata->name = $className;

        $reader = $this->getMockBuilder('DMR\Mapping\Reader')
            ->disableOriginalConstructor()
            ->setMethods(array('getCacheId'))
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

        $reflection = new \ReflectionClass($reader);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);
        $property->setValue($reader, $manager);

        $property = $reflection->getProperty('namespace');
        $property->setAccessible(true);
        $property->setValue($reader, $namespace);

        $this->assertSame($return, $reader->read($className));
    }

    public function testCachedDriverCached()
    {
        $namespace = 'Acme';
        $className = $namespace.'/Class';
        $cacheId = uniqid($className);

        $metadata = $this->getMockForAbstractClass('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->isMappedSuperclass = false;
        $metadata->name = $className;
        $metadata->reflClass = null;

        $reader = $this->getMockBuilder('DMR\Mapping\Reader')
            ->disableOriginalConstructor()
            ->setMethods(array('getCacheId'))
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

        $reflection = new \ReflectionClass($reader);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);
        $property->setValue($reader, $manager);

        $property = $reflection->getProperty('namespace');
        $property->setAccessible(true);
        $property->setValue($reader, $namespace);

        $return = $reader->read($className);
        $this->assertTrue(is_array($return));
        $this->assertEmpty($return);
    }

}
