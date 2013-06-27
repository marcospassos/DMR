<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Simple metadata reader.
 *
 * SimpleReader uses an implementation of ObjectManager
 * to get the necessary resources used by reader to read
 * the class's metadata. Note that you can just read objects
 * managed by the manager passed to the reader constructor.
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
