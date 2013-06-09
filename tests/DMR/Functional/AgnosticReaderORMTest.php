<?php

namespace DMR\Functional;

use DMR\Mapping\AgnosticReader;

/**
 * ODM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class AgnosticReaderORMTest extends AbstractReaderTest
{
	public function getReader()
	{
		return new AgnosticReader($this->getMockRegistries());
	}
	
	public function getNamespaced($class)
	{
		return 'DMR\Functional\Fixture\Model\ORM\\' . $class;
	}
}
