<?php

namespace DMR\Functional\Mock\Extension\Encoder\Mapping\Driver;

use DMR\Mapping\Driver\File;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Yaml as YmlParser;

/**
 * This is an YML mapping driver for File extension.
 * Used for extraction of extended metadata from YML
 * specificaly for File extension.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class Yaml extends File
{
    /**
     * {@inheritDoc}
     */
    public function read(ClassMetadata $classMetadata, array &$metadata)
    {
        $mapping = $this->getMapping($classMetadata->name);

        if (!isset($mapping['fields'])) {
            return;
        }

        foreach ($mapping['fields'] as $field => $fieldMapping) {
            if (!isset($fieldMapping['encoder'])) {
                continue;
            }

            $metadata['yaml']['field'] = $field;
            $metadata['yaml']['type'] = $fieldMapping['encoder']['type'];
            $metadata['yaml']['secret'] = $fieldMapping['encoder']['secret'];
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        return YmlParser::parse($file);
    }
}
