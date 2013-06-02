<?php

namespace DMR\Functional\Fixture\Model\ODM\Annotation;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DMR\Functional\Mock\Extension\Encoder\Mapping as Ext;

/**
 * @MongoDB\Document
 */
class User
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @Ext\Encode(type="sha1", secret="xxx")
     * @MongoDB\String
     */
    private $name;
}
