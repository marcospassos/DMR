<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Metadata factory.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class Reader implements ReaderInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Constructor.
     * 
     * @param ObjectManager $manager   The instance of ObjectManager
     * @param string        $namespace The namespace where the drivers are located
     */
    public function __construct(ObjectManager $manager, $namespace)
    {
        $this->manager = $manager;
        $this->namespace = $namespace;
        $originalDriver = $manager->getConfiguration()->getMetadataDriverImpl();
        $this->driver = DriverFactory::getDriver($originalDriver, $namespace);
    }

    /**
    * {@inheritDoc}
    */
    public function read($object)
    {
        $factory = $this->manager->getMetadataFactory();
        $meta = $factory->getMetadataFor(get_class($object));

        if ($meta->isMappedSuperclass) {
            return;
        }

        $cacheDriver = $factory->getCacheDriver();
        $cacheId = self::getCacheId($meta->name, $this->namespace);

        if ($cacheDriver && ($cached = $cacheDriver->fetch($cacheId)) !== false) {
            return $cached;
        }

        $metadata = array();
        // Collect metadata from inherited classes
        if (null !== $meta->reflClass) {
            foreach (array_reverse(class_parents($meta->name)) as $parentClass) {
                // read only inherited mapped classes
                if ($factory->hasMetadataFor($parentClass)) {
                    $class = $this->manager->getClassMetadata($parentClass);
                    $this->driver->read($class, $metadata);
                }
            }

            $this->driver->read($meta, $metadata);
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