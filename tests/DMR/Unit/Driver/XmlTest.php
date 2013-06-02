<?php

namespace DMR\Unit\Driver;

use DMR\Functional\Mock\Extension\Encoder\Mapping\Driver\Xml;
use org\bovigo\vfs\vfsStream;

/**
 * Xml base driver unit tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('example');
    }

    protected static function call()
    {
        $args = func_get_args();

        $obj = new Xml();
        $class = new \ReflectionClass('DMR\Mapping\Driver\Xml');
        $method = $class->getMethod($args[0]);
        $method->setAccessible(true);

        array_shift($args);

        return $method->invokeArgs($obj, $args);
    }

    public function testGetAttribute()
    {
        $value = 'Foo/Bar';
        $xml = new \SimpleXmlElement('<teste/>');
        $xml->addAttribute('name', $value);

        $this->assertEquals($value, self::call('getAttribute', $xml, 'name'));
    }

    public function testGetBooleanAttribute()
    {
        $value = 'Foo/Bar';
        $xml = new \SimpleXmlElement('<teste/>');
        $xml->addAttribute('trueValue', 'true');
        $xml->addAttribute('falseValue', 'false');

        $this->assertTrue(self::call('getBooleanAttribute', $xml, 'trueValue'));
        $this->assertFalse(self::call('getBooleanAttribute', $xml, 'falseValue'));
    }

    public function testIsAttributeSet()
    {
        $value = 'Foo/Bar';
        $xml = new \SimpleXmlElement('<teste/>');
        $xml->addAttribute('name', 'value');

        $this->assertTrue(self::call('isAttributeSet', $xml, 'name'));
        $this->assertFalse(self::call('isAttributeSet', $xml, 'invalid'));
    }

    public function testLoadMappingFile()
    {
        $root = vfsStream::setup('home');
        $types = array('mapped-superclass', 'entity', 'document');
        $class= 'Acme/Class';

        foreach ($types as $type) {
            $xml = sprintf('<doctrine-mapping><%1$s name="%2$s"></%1$s></doctrine-mapping>', $type, $class);
            $name = sprintf('%s.xml', $type);

            $file = vfsStream::newFile($name)->at($root)->setContent($xml);
            $url = $file->url();

            $this->assertTrue($root->hasChild($name));
            $this->assertEquals($xml, file_get_contents($url));

            $return = self::call('loadMappingFile', $url);
            $this->assertTrue(is_array($return));
            $this->assertArrayHasKey($class, $return);
            $this->assertInstanceOf('SimpleXmlElement', $return[$class]);
        }
    }

    public function testLoadUnexpectedMappingFile()
    {
        $root = vfsStream::setup('home');

        $xml = '<teste/>';
        $name = 'teste.xml';

        $file = vfsStream::newFile($name)->at($root)->setContent($xml);
        $url = $file->url();

        $this->assertTrue($root->hasChild($name));
        $this->assertEquals($xml, file_get_contents($url));

        $return = self::call('loadMappingFile', $url);
        $this->assertNull($return);
    }
}
