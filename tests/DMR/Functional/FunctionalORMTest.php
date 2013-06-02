<?php

namespace DMR\Functional;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;

/**
 * ORM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class FunctionalORMTest extends FunctionalTestCase
{
    protected $prefix = 'DMR\Functional\Fixture\Model\ORM\\';

    protected function setUp()
    {
        $reader = new AnnotationReader();
        $annotationDriver = new AnnotationDriver($reader);

        $namespace = array(__DIR__.'/Fixture/Mapping/Yaml' => 'DMR\Functional\Fixture\Model\ORM\Yaml');
        $yamlDriver = new SimplifiedYamlDriver($namespace);

        $namespace = array(__DIR__.'/Fixture/Mapping/Xml' => 'DMR\Functional\Fixture\Model\ORM\Xml');
        $xmlDriver = new SimplifiedXmlDriver($namespace);

        $chain = new MappingDriverChain();
        $chain->addDriver($xmlDriver, 'DMR\Functional\Fixture\Model\ORM\Xml');
        $chain->addDriver($yamlDriver, 'DMR\Functional\Fixture\Model\ORM\Yaml');
        $chain->addDriver($annotationDriver, 'DMR\Functional\Fixture\Model\ORM\Annotation');

        $this->manager = $this->getMockSqliteEntityManager($chain);
    }
}
