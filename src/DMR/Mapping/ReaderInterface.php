<?php

namespace DMR\Mapping;

/**
 * Metadata reader interface.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
interface ReaderInterface
{
    /**
    * Gets the metadata.
    *
    * @param object|string $object    The object instance from which metadata should be read or the class name
    * @param string        $namespace The drivers namespace
    *
    * @return array
    */
    public function read($object, $namespace);
}
