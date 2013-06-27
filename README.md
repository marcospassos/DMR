Doctrine Mapping Reader
=======================
PHP 5.3+ library that provides a simple and flexible way to load custom mapping data for [Doctrine 2.3+](https://github.com/doctrine/) projects.

It supports **Yaml**, **Xml** and **Annotation** drivers which will be chosen depending on currently used mapping driver for your domain objects.

Credits to [DoctrineExtensions](https://github.com/l3pp4rd/DoctrineExtensions), which big part of the code was inspired or extracted from.

[![Build Status](https://travis-ci.org/marcospassos/DMR.png)](https://travis-ci.org/marcospassos/DMR)

## What is this for?

This library is useful if you need read some data from Doctrine mapping files.

Imagine you have developed a DataGrid library and you need to know which properties should be exposed. You can easily create your own mapping in order to mark some properties for hidding and read before being rendered. This is a perfect case where this library can help you.

## How it works?

Doctrine provides several different ways for specifying mapping metadata:

- Docblock Annotations
- XML
- YAML

For each mapping metadata you want to support, you are going to create a driver for loading the relevant metadata that you need. Fortunately, this library does almost all the job for you and in the most of cases you need only read the data and store in the array that will be returned at the end.

Reading Doctrine's metadata is simple as:

```php
<?php
use DMR\Mapping\Reader;

// $manager should be an instance of Doctrine\Common\Persistence\ObjectManager
$reader = new SimpleReader($manager);
$data = $reader->read('Acme\Model\User', 'Acme\Doctrine\ExtensionNamespace');
// or $reader->read($user, 'Acme\Doctrine\ExtensionNamespace');
```

**Are you serious?**
Absolutely. Keep reading.

## Try it!

### Installation

DMR is installed via [Composer](http://getcomposer.org/). To install, simply add it to your composer.json file:

```json
{
    "require": {
        "dmr/dmr": "0.1.*-dev"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Create your drivers
In order to read the desired information in the Doctrine's mapping files, you need to implement a driver for each mapping type that should be supported. The library will try to find the drivers in the namespace `Your\Extension\Mapping\Driver`. If any driver correspondent to the current mapping type used by Doctrine is found, the library will try to load the Annotation driver as fallback (if not found, an exception will be thrown).

This is a suggestion for your project structure:

```
project
    Doctrine
        YourExtension
            Mapping
                Driver
                    Annotation.php
                    Xml.php
                    Yaml.php
                Annotations.php
```

#### Driver examples

##### Annotation
```php
<?php
namespace Acme\Doctrine\YourExtension\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class Encode extends Annotation
{
    public $type = 'md5'; // default value
    public $secret;
}
```

```php
<?php
namespace Acme\Doctrine\YourExtension\Mapping\Driver;

use DMR\Mapping\Driver\AbstractAnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class Annotation extends AbstractAnnotationDriver
{
    const ANNOTATION = 'Acme\Doctrine\YourExtension\Mapping\Encode';

    public function read(ClassMetadata $classMetadata, array &$metadata)
    {
        $class = $classMetadata->getReflectionClass();

        foreach ($class->getProperties() as $property) {
            $encode = $this->reader->getPropertyAnnotation($property, self::ANNOTATION);

            if ($encode == null) {
                continue;
            }

            $field = $property->getName();
            $metadata['field'] = $field;
            $metadata['type'] = $encode->type;
            $metadata['secret'] = $encode->secret;
        }
    }
}

```

##### Xml

```php
namespace Acme\Doctrine\YourExtension\Mapping\Driver;

use DMR\Mapping\Driver\Xml as BaseXml;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class Xml extends BaseXml
{
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

            // Required for object document mapping
            $field = $this->getAttribute($mapping, 'fieldName') ?: $this->getAttribute($mapping, 'name');

            $metadata['field'] = $field;
            $metadata['type'] = $this->getAttribute($mapping->encode, 'type') ?: 'md5';
            $metadata['secret'] = $this->getAttribute($mapping->encode, 'secret');
        }
    }
}

```

##### Yaml

```php
namespace Acme\Doctrine\YourExtension\Mapping\Driver;

use DMR\Mapping\Driver\File;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Yaml as YmlParser;

class Yaml extends File
{
    public function read(ClassMetadata $classMetadata, array &$metadata)
    {
        $mapping = $this->getMapping($classMetadata->name);

        if (!isset($mapping['fields'])) {
            return;
        }

        foreach ($mapping['fields'] as $field => $fieldMapping) {
            if (!isset($fieldMapping['encode'])) {
                continue;
            }

            $metadata['field'] = $field;
            $metadata['type'] = isset($fieldMapping['encode']['type']) ? $fieldMapping['encode']['type'] : 'md5';
            $metadata['secret'] = $fieldMapping['encode']['secret'];
        }
    }

    protected function loadMappingFile($file)
    {
        return YmlParser::parse($file);
    }
}
```

### Mapping definitions

**Note:** The following examples use the ORM mapping as reference but ODM is also supported.

#### Annotation
```php
namespace Acme\Model;

use Acme\Doctrine\YourExtension\Mapping as Ext;

/**
 * @ORM\Entity
 */
class User
{
    /**
     * @Ext\Encode(type="sha1", secret="xxx")
     * @ORM\Column(length=64)
     */
    private $password;
}
```

#### Xml
```xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                      http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="Acme\Model\User">
        <field name="password" type="string">
            <encode type="sha1" secret="xxx" />
        </field>
    </entity>

</doctrine-mapping>
```

#### Yaml
```yaml
Acme\Model\User:
  fields:
    password:
      encode:
        type: sha1
        secret: xxx
      type: string
```

### Reading the data

Reading the data is pretty simple. Currently there are two metadata readers available:

#### Simple Reader

`SimpleReader` uses an implementation of `Doctrine\Common\Persistence\ObjectManager` to get the necessary resources used by reader to read the class's metadata. Note that you can just read objects managed by the manager passed to the reader constructor.

```php
<?php
use DMR\Mapping\Reader;

// $manager should be an instance of Doctrine\Common\Persistence\ObjectManager
$reader = new SimpleReader($manager);
$data = $reader->read('Acme\Model\User', 'Acme\Doctrine\ExtensionNamespace');
// or $reader->read($user, 'Acme\Doctrine\ExtensionNamespace');

var_dump($data);
```

The above example will output:

```
array(3) {
  'field' =>
  string(8) "password"
  'type' =>
  string(4) "sha1"
  'secret' =>
  string(3) "xxx"
}
```

#### Agnostic Reader

`AgnosticReader` uses a `Doctrine\Common\Persistence\ManagerRegistry` to get the necessary resources used by reader to read the class's metadata. The advantage of this implementation over the `SimpleReader` is that this one automatically guesses the manager based on the given object so that you can read the metadata's from an entity or a document in a transparent way.

```php
<?php
use DMR\Mapping\Reader;

// $registries should be an array of Doctrine\Common\Persistence\ManagerRegistry
$reader = new AgnosticReader($registries);
$data = $reader->read('Acme\Entity\User', 'Acme\Doctrine\ExtensionNamespace');
$data = $reader->read('Acme\Document\User', 'Acme\Doctrine\ExtensionNamespace');
// or $reader->read($user, 'Acme\Doctrine\ExtensionNamespace');

var_dump($data);
```

The above example will output:

```
array(3) {
  'field' =>
  string(8) "password"
  'type' =>
  string(4) "sha1"
  'secret' =>
  string(3) "xxx"
}
```

**That's it!**

## Tests

DMR's tests covers 100% of the code.

[![Build Status](https://travis-ci.org/marcospassos/DMR.png)](https://travis-ci.org/marcospassos/DMR)

### Running the Tests

The tests were written using PHPUnit.

#### Installing dependecies

In order to run the tests, some libraries used in the tests cases must be installed before:

    $ cd dmr
    $ php composer.phar install --dev

#### Launch the Test Suite

In the DMR root directory:

    $ phpunit --coverage-text

Is it green?

## Feedback

**Please provide feedback!** We want to make this library useful in as many projects as possible. Please raise a Github issue, and point out what you do and don't like, or fork the project and make suggestions. **No issue is too small.**