<?php

namespace DMR\Functional\Mock\Extension\Encoder\Mapping\Driver;

use DMR\Mapping\Driver\AbstractAnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * This is an annotation mapping driver for File extension.
 * Used for extraction of extended metadata from Annotations
 * specificaly for File extension.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class Annotation extends AbstractAnnotationDriver
{
    const ANNOTATION = 'DMR\Functional\Mock\Extension\Encoder\Mapping\Encode';

    /**
     * {@inheritDoc}
     */
    public function read(ClassMetadata $classMetadata, array &$metadata)
    {
        $class = $classMetadata->getReflectionClass();

        foreach ($class->getProperties() as $property) {
            $encode = $this->reader->getPropertyAnnotation($property, self::ANNOTATION);

            if ($encode == null) {
                continue;
            }

            $field = $property->getName();
            $metadata['annotation']['field'] = $field;
            $metadata['annotation']['type'] = $encode->type;
            $metadata['annotation']['secret'] = $encode->secret;
        }
    }
}
