<?php

namespace DMR\Functional;

use DMR\Mapping\SimpleReader;

/**
 * ORM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class SimpleReaderORMTest extends AbstractReaderTest
{
	public function getReader()
	{
		$driver = $this->getMockORMMappingDriver();

		return new SimpleReader($this->getMockSqliteEntityManager($driver));
	}
	
	public function getNamespaced($class)
	{
		return 'DMR\Functional\Fixture\Model\ORM\\' . $class;
	}
}
