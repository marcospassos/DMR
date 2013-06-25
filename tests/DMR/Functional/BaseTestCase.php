<?php

namespace DMR\Functional;

use Doctrine\Common\EventManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
// ORM specific
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as AnnotationDriverORM;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
// ODM specific
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver as AnnotationDriverODM;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;

/**
 * Functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    const DRIVER_NAMESPACE = 'DMR\Functional\Mock\Extension\Encoder';

    /**
     * @var EventManager
     */
    protected $evm;

    public function getMockRegistries()
    {
    	$registries = array();

    	$registry = $this->getMockBuilder('Doctrine\Common\Persistence\AbstractManagerRegistry')
    		->disableOriginalConstructor()
    		->setMethods(array('getManagerForClass'))
    		->getMockForAbstractClass();

        $instance = $this;

    	$callback = function ($class) use ($instance) {

    		if (strpos($class, 'ORM') > -1) {
    			$chain = $instance->getMockORMMappingDriver();

    			return $instance->getMockSqliteEntityManager($chain);
    		}

    		if (strpos($class, 'ODM') > -1) {
    			$chain = $instance->getMockMongoDBMappingDriver();

    			return $instance->getMockMongoDBDocumentManager($chain);
    		}
    	};
    	
    	$this->assertNull($callback(''));
    	$this->assertInstanceOf('Doctrine\ORM\EntityManager', $callback('ORM'));
    	$this->assertInstanceOf('Doctrine\ODM\MongoDB\DocumentManager', $callback('ODM'));

    	$registry->expects($this->any())
    		->method('getManagerForClass')
    		->will($this->returnCallback($callback));
    	
    	$registries[] = $registry;
    	
    	$registry = $this->getMockBuilder('Doctrine\Common\Persistence\AbstractManagerRegistry')
	    	->disableOriginalConstructor()
	    	->setMethods(array('getManagerForClass'))
	    	->getMockForAbstractClass();
    	
    	$registry->expects($this->any())
	    	->method('getManagerForClass')
	    	->will($this->returnCallback($callback));
    	 
    	$registries[] = $registry;
    	
    	return $registries;
    }
    
    protected function getMockMongoDBMappingDriver()
    {
    	$reader = new AnnotationReader();
    	$annotationDriver = new AnnotationDriverODM($reader);
    	
    	$namespace = array(__DIR__.'/Fixture/Mapping/Yaml' => 'DMR\Functional\Fixture\Model\ODM\Yaml');
    	$locator = new SymfonyFileLocator($namespace, '.odm.yml');
    	$yamlDriver = new YamlDriver($locator, '.odm.xml');
    	
    	$namespace = array(__DIR__.'/Fixture/Mapping/Xml' => 'DMR\Functional\Fixture\Model\ODM\Xml');
    	$locator = new SymfonyFileLocator($namespace, '.odm.xml');
    	$xmlDriver = new XmlDriver($locator, $locator);
    	
    	$chain = new MappingDriverChain();
    	$chain->addDriver($xmlDriver, 'DMR\Functional\Fixture\Model\ODM\Xml');
    	$chain->addDriver($yamlDriver, 'DMR\Functional\Fixture\Model\ODM\Yaml');
    	$chain->addDriver($annotationDriver, 'DMR\Functional\Fixture\Model\ODM\Annotation');
    	
    	return $chain;
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

    protected function getMockORMMappingDriver()
    {
    	$reader = new AnnotationReader();
	    $annotationDriver = new AnnotationDriverORM($reader);
	    
	    $namespace = array(__DIR__.'/Fixture/Mapping/Yaml' => 'DMR\Functional\Fixture\Model\ORM\Yaml');
	    $yamlDriver = new SimplifiedYamlDriver($namespace);
	    
	    $namespace = array(__DIR__.'/Fixture/Mapping/Xml' => 'DMR\Functional\Fixture\Model\ORM\Xml');
	    $xmlDriver = new SimplifiedXmlDriver($namespace);
	    
	    $chain = new MappingDriverChain();
	    $chain->addDriver($xmlDriver, 'DMR\Functional\Fixture\Model\ORM\Xml');
	    $chain->addDriver($yamlDriver, 'DMR\Functional\Fixture\Model\ORM\Yaml');
	    $chain->addDriver($annotationDriver, 'DMR\Functional\Fixture\Model\ORM\Annotation');
    	 
    	return $chain;
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
            ->will($this->returnValue($mappingDriver))
        ;

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
            ->will($this->returnValue($mappingDriver))
        ;

        return $config;
    }
}
