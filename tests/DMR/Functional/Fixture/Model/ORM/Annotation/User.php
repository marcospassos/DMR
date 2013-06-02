<?php

namespace DMR\Functional\Fixture\Model\ORM\Annotation;

use Doctrine\ORM\Mapping as ORM;
use DMR\Functional\Mock\Extension\Encoder\Mapping as Ext;

/**
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @Ext\Encode(type="sha1", secret="xxx")
     * @ORM\Column(length=64)
     */
    private $name;
}
