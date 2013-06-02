<?php

namespace DMR\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;

/**
 * ODM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class FunctionalODMTest extends FunctionalTestCase
{
    protected $prefix = 'DMR\Functional\Fixture\Model\ODM\\';

    protected function setUp()
    {
        $reader = new AnnotationReader();
        $annotationDriver = new AnnotationDriver($reader);

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

        $this->manager = $this->getMockMongoDBDocumentManager($chain);
    }
}
