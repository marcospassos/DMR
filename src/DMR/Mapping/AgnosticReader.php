<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Agnostic metadata reader.
 *
 * AgnosticReader uses a ManagerRegistry to get the necessary
 * resources used by reader to read the class's metadata. The
 * advantage of this implementation over the SimpleReader is
 * that this one automatically guesses the manager based on
 * the given object so that you can read the metadata's from
 * an entity or a document in a transparent way.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class AgnosticReader extends AbstractReader
{
    /**
     * @var array
     */
    protected $registries;

    /**
     * Constructor.
     */
    public function __construct(array $registries = null)
    {
    	$this->registries = array();

    	if ($registries) {
    		foreach ($registries as $registry) {
    			if (!$registry instanceof ManagerRegistry) {
    				throw new \RuntimeException('The registry must implement interface Doctrine\Common\Persistence\ManagerRegistry.');
    			}

    			$this->addManagerRegistry($registry);
    		}
    	}
    }

    /**
     * Adds a manager registry.
     * 
     * @param ManagerRegistry $registry
     */
    public function addManagerRegistry(ManagerRegistry $registry)
    {
    	if (!in_array($registry, $this->registries, true)) {
    		$this->registries[] = $registry;
    	}
    }
    
    public function getRegistries()
    {
    	return $this->registries;
    }

    protected function getManagerForClass($class)
    {
    	foreach ($this->registries as $registry) {
    		if (($manager = $registry->getManagerForClass($class)) != null) {
    			return $manager;
    		}
    	}

    	throw new \RuntimeException(sprintf('There is no manager registered for class "%s"', $class));
    }
}
