Doctrine Mapping Reader
=======================
PHP 5.3+ library that provides a simple and flexible way to load custom mapping data for [Doctrine 2.3+](https://github.com/doctrine/) projects.

It supports **Yaml**, **Xml** and **Annotation** drivers which will be chosen depending on currently used mapping driver for your domain objects.

Credits to [DoctrineExtensions](https://github.com/l3pp4rd/DoctrineExtensions), which big part of the code was inspired or extracted from.

## What is this for?

This library is useful if you need read some data from Doctrine mapping files.

Imagine you have developed a DataGrid library and you need to know which properties should be exposed. You can easily create your own mapping in order to mark some properties for hidding and read before being rendered. This is a perfect case where this library can help you.

## How it works?

Doctrine provides several different ways for specifying object-relational mapping metadata:

- Docblock Annotations
- XML
- YAML
For each mapping metadata you want to support, you are going to create a driver for loading the relevant metadata that you need. Fortunately, this library does almost all the job for you and in the most of cases you need only read the data and store in the array that will be returned at the end.

## Try it!

### Create your drivers

To-do.

## To-do

- Write some tests
- Enable Travis
- Add composer file
