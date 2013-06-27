<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Simple metadata reader.
 *
 * Uses an ObjectManager to get the necessary resources
 * to read the metadata.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class SimpleReader extends AbstractReader
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Constructor.
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function getManagerForClass($class)
    {
    	return $this->manager;
    }
}
