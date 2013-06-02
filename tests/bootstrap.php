<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

define('TESTS_TEMP_DIR', __DIR__ . '/temp');

$basePath = dirname(__DIR__);
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('DMR', array($basePath.'/src/', $basePath.'/tests/'));
$loader->register();

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
