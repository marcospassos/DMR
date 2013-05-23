<?php

namespace DMR\Mapping\Driver;

use DMR\Mapping\Driver\DriverInterface;
use Doctrine\Common\Annotations\Reader;

/**
 * Annotation driver interface, provides method to set
 * custom annotation reader.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
interface AnnotationDriverInterface extends DriverInterface
{
    /**
     * Sets the annotation reader.
     * 
     * @param Reader $reader
     */
    public function setAnnotationReader(Reader $reader);
}