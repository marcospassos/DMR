<?php

namespace DMR\Mapping;

/**
 * Metadata factory interface.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
interface ReaderInterface
{
    /**
    * Gets the metadata.
    * 
    * @param object $object The object instance from which metadata should be read
    * 
    * @return array
    */
    public function read($object);
}