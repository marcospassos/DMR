<?php

namespace DMR\Functional;

use DMR\Mapping\Reader;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
// ORM specific
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as AnnotationDriverORM;
use Doctrine\ORM\EntityManager;
// ODM specific
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as AnnotationDriverODM;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;

/**
 * Functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    const DRIVER_NAMESPACE = 'DMR\Functional\Mock\Extension\Encoder';

    /**
     * @var EventManager
     */
    protected $evm;

    /**
     * @var string
     */
    protected $prefix;

    public function getInstance($class)
    {
        $class = $this->prefix . $class;

        return new $class;
    }

    public function testExceptionNoDrivers()
    {
        $this->setExpectedException('RuntimeException');

        $reader = new Reader($this->manager, 'Invalid\Namespace');
    }

    public function testXmlMapping()
    {
        $reader = new Reader($this->manager, self::DRIVER_NAMESPACE);
        $metadata = $reader->read($this->getInstance('Xml\User'));

        $this->assertEquals('name', $metadata['xml']['field']);
        $this->assertEquals('sha1', $metadata['xml']['type']);
        $this->assertEquals('xxx', $metadata['xml']['secret']);
    }

    public function testInheritanceXmlMapping()
    {
        $reader = new Reader($this->manager, self::DRIVER_NAMESPACE);
        $metadata = $reader->read($this->getInstance('Xml\Child'));

        $this->assertEquals('name', $metadata['xml']['field']);
        $this->assertEquals('sha1', $metadata['xml']['type']);
        $this->assertEquals('xxx', $metadata['xml']['secret']);
    }

    public function testYamlMapping()
    {
        $reader = new Reader($this->manager, self::DRIVER_NAMESPACE);
        $metadata = $reader->read($this->getInstance('Yaml\User'));

        $this->assertEquals('name', $metadata['yaml']['field']);
        $this->assertEquals('sha1', $metadata['yaml']['type']);
        $this->assertEquals('xxx', $metadata['yaml']['secret']);
    }

    public function testAnnotationMapping()
    {
        $reader = new Reader($this->manager, self::DRIVER_NAMESPACE);
        $metadata = $reader->read($this->getInstance('Annotation\User'));

        $this->assertEquals('name', $metadata['annotation']['field']);
        $this->assertEquals('sha1', $metadata['annotation']['type']);
        $this->assertEquals('xxx', $metadata['annotation']['secret']);
    }

    /**
     * DocumentManager mock object with annotation mapping driver.
     *
     * @param MappingDriver $mappingDriver
     *
     * @return DocumentManager
     */
    protected function getMockMongoDBDocumentManager(MappingDriver $mappingDriver = null)
    {
        $conn = $this->getMock('Doctrine\\MongoDB\\Connection');
        $config = $this->getMockAnnotatedODMMongoDBConfig($mappingDriver);

        $dm = DocumentManager::create($conn, $config, $this->getEventManager());

        return $dm;
    }

    /**
     * EntityManager mock object together with annotation mapping
     * driver and pdo_sqlite database in memory.
     *
     * @param MappingDriver $mappingDriver
     *
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager(MappingDriver $mappingDriver = null)
    {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $config = $this->getMockAnnotatedORMConfig($mappingDriver);
        $em = EntityManager::create($conn, $config, $this->getEventManager());

        return $em;
    }

    /**
     * Returns an implementation of Reader.
     *
     * @return Reader
     */
    protected function getAnnotationReader()
    {
        $reader = new CachedReader(new AnnotationReader(), new ArrayCache());

        return $reader;
    }

    /**
     * Creates default mapping driver for ORM.
     *
     * @return \Doctrine\ORM\Mapping\Driver\Driver
     */
    protected function getDefaultORMMetadataDriverImplementation()
    {
        return new AnnotationDriverORM($this->getAnnotationReader());
    }

    /**
     * Creates default mapping driver for ODM.
     *
     * @return \Doctrine\ODM\MongoDB\Mapping\Driver\Driver
     */
    protected function getDefaultMongoODMMetadataDriverImplementation()
    {
        return new AnnotationDriverODM($this->getAnnotationReader());
    }

    /**
     * Build event manager.
     *
     * @return EventManager
     */
    private function getEventManager()
    {
        if (null === $this->evm) {
            $this->evm = new EventManager;
        }

        return $this->evm;
    }

    /**
     * Get annotation mapping configuration for ODM.
     *
     * @param MappingDriver $mappingDriver
     *
     * @return Doctrine\ORM\Configuration
     */
    private function getMockAnnotatedODMMongoDBConfig(MappingDriver $mappingDriver = null)
    {
        $config = $this->getMock('Doctrine\\ODM\\MongoDB\\Configuration');
        $config->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(TESTS_TEMP_DIR));

        $config->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'))
        ;

        $config->expects($this->once())
            ->method('getHydratorDir')
            ->will($this->returnValue(TESTS_TEMP_DIR))
                ;

        $config->expects($this->once())
            ->method('getHydratorNamespace')
            ->will($this->returnValue('Hydrator'))
        ;

        $config->expects($this->any())
            ->method('getDefaultDB')
            ->will($this->returnValue('dmr_test'))
        ;

        $config->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true))
        ;

        $config->expects($this->once())
            ->method('getAutoGenerateHydratorClasses')
            ->will($this->returnValue(true))
        ;

        $config->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ODM\\MongoDB\\Mapping\\ClassMetadataFactory'))
        ;

        $config
            ->expects($this->any())
            ->method('getMongoCmd')
            ->will($this->returnValue('$'))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultCommitOptions')
            ->will($this->returnValue(array('safe' => true)))
        ;

        if (null === $mappingDriver) {
            $mappingDriver = $this->getDefaultMongoODMMetadataDriverImplementation();
        }

        $config->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver));

        return $config;
    }

    /**
     * Get annotation mapping configuration for ORM.
     *
     * @param MappingDriver $mappingDriver
     *
     * @return Doctrine\ORM\Configuration
     */
    private function getMockAnnotatedORMConfig(MappingDriver $mappingDriver = null)
    {
        $config = $this->getMock('Doctrine\ORM\Configuration');
        $config->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(TESTS_TEMP_DIR))
        ;

        $config->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'))
        ;

        $config->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true))
        ;

        $config->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultRepositoryClassName')
            ->will($this->returnValue('Doctrine\\ORM\\EntityRepository'))
        ;

        $config
            ->expects($this->any())
            ->method('getQuoteStrategy')
            ->will($this->returnValue(new DefaultQuoteStrategy()))
        ;

        $config
            ->expects($this->any())
            ->method('getNamingStrategy')
            ->will($this->returnValue(new DefaultNamingStrategy()))
        ;

        if (null === $mappingDriver) {
            $mappingDriver = $this->getDefaultORMMetadataDriverImplementation();
        }

        $config->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver));

        return $config;
    }
}
