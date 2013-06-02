<?php

namespace DMR\Functional\Mock\Extension\Encoder\Mapping\Driver;

use DMR\Mapping\Driver\Xml as BaseXml;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * This is an XML mapping driver for File extension.
 * Used for extraction of extended metadata from XML
 * specificaly for File extension.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class Xml extends BaseXml
{
    /**
     * {@inheritDoc}
     */
    public function read(ClassMetadata $classMetadata, array &$metadata)
    {
        $mapping = $this->getMapping($classMetadata->name);

        if (!isset($mapping->field)) {
            return;
        }

        foreach ($mapping->field as $mapping) {
            if (!isset($mapping->encode)) {
                continue;
            }

            $field = $this->getAttribute($mapping, 'fieldName')
                    ? $this->getAttribute($mapping, 'fieldName')
                    : $this->getAttribute($mapping, 'name');

            $metadata['xml']['field'] = $field;
            $metadata['xml']['type'] = $this->getAttribute($mapping->encode, 'type');
            $metadata['xml']['secret'] = $this->getAttribute($mapping->encode, 'secret');
        }
    }
}
