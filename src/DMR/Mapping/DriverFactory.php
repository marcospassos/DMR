<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use DMR\Mapping\Driver\AnnotationDriverInterface;

/**
 * Metadata factory.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class DriverFactory
{
    /**
     * Returns the annotation reader.
     *
     * @return \Doctrine\Common\Annotations\Reader
     */
    protected static function getAnnotationReader()
    {
        return new CachedReader(new AnnotationReader(), new ArrayCache());
    }

    /**
     * Creates a driver based on original driver that is being used by Doctrine.
     *
     * @param MappingDriver $originalDriver The instance of MappingDriver
     * @param string        $namespace      The namespace where the drivers are located
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @return DriverInterface
     */
    public static function getDriver(MappingDriver $originalDriver, $namespace)
    {
        /*if (\Doctrine\Common\Version::compare('2.3.0') > -1) {
            throw new \Exception('The DMR library requires Doctrine 2.3.0 or higher.');
        }*/

        if ($originalDriver instanceof MappingDriverChain) {
            $driver = new Driver\Chain();
            foreach ($originalDriver->getDrivers() as $nestedNamespace => $nestedDriver) {
                $driver->addDriver(static::getDriver($nestedDriver, $namespace), $nestedNamespace);
            }

            if ($originalDriver->getDefaultDriver() !== null) {
                $driver->setDefaultDriver(static::getDriver($originalDriver->getDefaultDriver(), $namespace));
            }

            return $driver;
        }

        preg_match('/(?P<type>Xml|Yaml|Annotation)Driver$/', get_class($originalDriver), $m);
        $type = isset($m['type']) ? $m['type'] : null;
        $driverClass = sprintf('%s\Mapping\Driver\%s', $namespace, $type);

        // Fallback driver
        if (!$type || !class_exists($driverClass)) {
            $driverClass = sprintf('%s\Mapping\Driver\Annotation', $namespace);;

            if (!class_exists($driverClass)) {
                throw new \RuntimeException(sprintf('Failed to fallback to annotation driver: (%s), extension driver was not found.', $driverClass));
            }
        }

        $driver = new $driverClass();
        $driver->setOriginalDriver($originalDriver);

        if ($driver instanceof Driver\File) {
            $driver->setLocator($originalDriver->getLocator());
        } elseif ($driver instanceof AnnotationDriverInterface) {
            $reader = static::getAnnotationReader();
            $driver->setAnnotationReader($reader);
        }

        return $driver;
    }
}
