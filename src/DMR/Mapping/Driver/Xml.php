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

        return isset($attributes[$name]);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        $xmlElement = simplexml_load_file($file);
        $types = array('mapped-superclass', 'entity', 'document');

        foreach ($types as $type) {
            if (isset($xmlElement->$type)) {
                $className = $this->getAttribute($xmlElement->$type, 'name');

                return array($className => $xmlElement->$type);
            }
        }

        return null;
    }
}
