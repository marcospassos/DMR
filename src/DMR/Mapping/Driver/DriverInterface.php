<?php

namespace DMR\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * Driver interface.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
interface DriverInterface
{
    /**
     * Read metadata for a single mapped class.
     *
     * @param object $classMetadata The instance of ClassMetadata
     * @param array  &$metadata     The object or array where metadata should be written
     */
    public function read(ClassMetadata $classMetadata, array &$metadata);

    /**
     * Passes in the original driver.
     *
     * @param MappingDriver $driver
     */
    public function setOriginalDriver(MappingDriver $driver);

    /**
     * Returns the original driver.
     *
     * @return MappingDriver
     */
    public function getOriginalDriver();
}
