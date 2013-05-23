<?php

namespace DMR\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * The mapping FileDriver abstract class, defines the metadata
 * extraction function common among all drivers used on these
 * extensions by file based drivers.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class File implements DriverInterface
{
    /**
     * @var FileLocator
     */
    protected $locator;

    /**
     * @var MappingDriver|null
     */
    protected $originalDriver;

    /**
     * Loads a mapping file with the given name and returns a map
     * from class/entity names to their corresponding elements.
     *
     * @param string $file The mapping file to load.
     * 
     * @return array
     */
    abstract protected function loadMappingFile($file);

    /**
     * {@inheritDoc}
     */
    public function setOriginalDriver(MappingDriver $driver)
    {
    	$this->originalDriver = $driver;
    }

    /**
     * Sets the file locator.
     *
     * @param FileLocator $locator
     */
    public function setLocator(FileLocator $locator)
    {
    	$this->locator = $locator;
    }

    /**
     * Tries to get a mapping for a given class
     *
     * @param string $className
     * 
     * @return null|array|object
     */
    public function getMapping($className)
    {
        // Try loading mapping from original driver first
        $mapping = null;
        if (null != $this->originalDriver) {
            if ($this->originalDriver instanceof FileDriver) {
                $mapping = $this->originalDriver->getElement($className);
            }
        }

        // If no mapping found try to load mapping file again
        if (null == $mapping) {
            $mappings = $this->loadMappingFile($this->locator->findMappingFile($className));
            $mapping = $mappings[$className];
        }

        return $mapping;
    }
}
