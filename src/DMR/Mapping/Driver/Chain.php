<?php

namespace DMR\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * The chain mapping driver enables chained mapping driver support.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class Chain implements DriverInterface
{
    /**
     * @var Driver|null
     */
    private $defaultDriver;

    /**
     * @var Driver[]
     */
    private $drivers = array();

    /**
     * Adds a nested driver.
     *
     * @param DriverInterface $driver    The instance of DriverInterface
     * @param string          $namespace The namespace
     */
    public function addDriver(DriverInterface $driver, $namespace)
    {
        $this->drivers[$namespace] = $driver;
    }

    /**
     * Gets the array of nested drivers.
     *
     * @return Driver[] $drivers
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Gets the default driver.
     *
     * @return Driver|null
     */
    public function getDefaultDriver()
    {
        return $this->defaultDriver;
    }

    /**
     * Sets the default driver.
     *
     * @param DriverInterface $driver
     */
    public function setDefaultDriver(DriverInterface $driver)
    {
        $this->defaultDriver = $driver;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ClassMetadata $classMetadata, array &$metatada)
    {
        foreach ($this->drivers as $namespace => $driver) {
            if (strpos($classMetadata->name, $namespace) === 0) {
                $driver->read($classMetadata, $metatada);

                return;
            }
        }

        if (null !== $this->defaultDriver) {
            $this->defaultDriver->read($classMetadata, $metatada);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalDriver()
    {
        // not needed here
    }

    /**
     * {@inheritDoc}
     */
    public function setOriginalDriver(MappingDriver $driver)
    {
        // not needed here
    }
}
