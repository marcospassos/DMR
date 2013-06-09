<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Metadata factory.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class AbstractReader implements ReaderInterface
{
	/**
	 * Returns the manager for an object.
	 * 
	 * @param string $class
	 */
    abstract protected function getManagerForClass($class);

    /**
     * Returns the driver implementation correspondent to
     * current in use natively by Doctrine.
     * 
     * @param ObjectManager $manager   The impementation of ObjectManager
     * @param string        $namespace The namespace prefix where the drivers are
     * 
     * @return DMR\Mapping\DriverInterface
     */
    public function getDriverForManager(ObjectManager $manager, $namespace)
    {
    	return DriverFactory::getDriver($manager->getConfiguration()->getMetadataDriverImpl(), $namespace);
    }

    /**
    * {@inheritDoc}
    */
    public function read($object, $namespace)
    {
        $className = is_object($object) ? get_class($object) : $object;
        $manager = $this->getManagerForClass($className);
        $factory = $manager->getMetadataFactory();
        $meta = $factory->getMetadataFor($className);

        if ($meta->isMappedSuperclass) {
            return;
        }

        $cacheDriver = $factory->getCacheDriver();
        $cacheId = static::getCacheId($meta->name, $namespace);

        if ($cacheDriver && ($cached = $cacheDriver->fetch($cacheId)) !== false) {
            return $cached;
        }

        $metadata = array();
        // Collect metadata from inherited classes
        if (null !== $meta->reflClass) {
        	$driver = $this->getDriverForManager($manager, $namespace);

            foreach (array_reverse(class_parents($meta->name)) as $parentClass) {
                // read only inherited mapped classes
                if ($factory->hasMetadataFor($parentClass)) {
                    $class = $manager->getClassMetadata($parentClass);
                    $driver->read($class, $metadata);
                }
            }

            $driver->read($meta, $metadata);
        }

        /* Cache the metadata (even if it's empty). Caching empty
         * metadata will prevent re-parsing non-existent annotations
         */
        if ($cacheDriver) {
            $cacheDriver->save($cacheId, $metadata, null);
        }

        return $metadata;
    }

    /**
     * Gets the cache id.
     *
     * @param string $className The class name
     * @param string $namespace The namespace
     *
     * @return string
     */
    public static function getCacheId($className, $namespace)
    {
        return $className . '\\$' . strtoupper(str_replace('\\', '_', $namespace)) . '_CLASSMETADATA';
    }
}
