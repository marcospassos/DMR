<?php

namespace DMR\Functional\Mock\Extension\Encoder\Mapping;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class Encode extends Annotation
{
    public $type = 'md5';
    public $secret;
}
