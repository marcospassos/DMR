<?php

namespace DMR\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * This is an abstract class to implement common functionality
 * for extension annotation mapping drivers.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class AbstractAnnotationDriver implements AnnotationDriverInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * MappingDriver
     */
    protected $originalDriver;

    /**
     * {@inheritDoc}
     */
    public function getAnnotationReader()
    {
        return $this->reader;
    }

    /**
     * {@inheritDoc}
     */
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function setOriginalDriver(MappingDriver $driver)
    {
        $this->originalDriver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalDriver()
    {
        return $this->originalDriver;
    }
}
