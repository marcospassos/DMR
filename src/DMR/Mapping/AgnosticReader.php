<?php

namespace DMR\Mapping;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Metadata factory.
 *
 * This reader uses a ManagerRegistry to get the necessary
 * resources to read the metadata. The advantage of this
 * reader over SimpleReader is this automatically guess
 * the manager based on the given object. Once this reader
 * is storage agnostic, you an read metadata's from an entity
 * or a document in a transparent way.
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
