<?php

namespace DMR\Mapping\Driver;

use \SimpleXMLElement;

/**
 * The Xml driver abstract class, defines the metadata extraction
 * function common among all drivers used on file based drivers.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
abstract class Xml extends File
{
    const DOCTRINE_NAMESPACE_URI = 'http://doctrine-project.org/schemas/orm/doctrine-mapping';

    /**
     * Gets attribute value.
     *
     * @param SimpleXMLElement $node The instance of SimpleXMLElement
     * @param string           $name The name of attribute
     * 
     * @return string
     */
    protected function getAttribute(SimpleXmlElement $node, $name)
    {
        $attributes = $node->attributes();

        return (string) $attributes[$name];
    }

    /**
     * Gets boolean attribute value.
     *
     * @param SimpleXMLElement $node The instance of SimpleXMLElement
     * @param string           $name The name of attribute
     * 
     * @return boolean
     */
    protected function getBooleanAttribute(SimpleXmlElement $node, $name)
    {
        return 'true' === strtolower($this->getAttribute($node, $name));
    }

    /**
     * Checks whether an attribute exist under a specific node.
     *
     * @param SimpleXMLElement $node The instance of SimpleXMLElement
     * @param string           $name The name of attribute
     * 
     * @return boolean
     */
    protected function isAttributeSet(SimpleXmlElement $node, $name)
    {
        $attributes = $node->attributes();

        return isset($attributes[$attributeName]);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        $result = array();
        $xmlElement = simplexml_load_file($file);
        $xmlElement = $xmlElement->children(self::DOCTRINE_NAMESPACE_URI);

        if (isset($xmlElement->entity)) {
            foreach ($xmlElement->entity as $entityElement) {
                $entityName = $this->getAttribute($entityElement, 'name');
                $result[$entityName] = $entityElement;
            }
        } else if (isset($xmlElement->{'mapped-superclass'})) {
            foreach ($xmlElement->{'mapped-superclass'} as $mappedSuperClass) {
                $className = $this->getAttribute($mappedSuperClass, 'name');
                $result[$className] = $mappedSuperClass;
            }
        }

        return $result;
    }
}
