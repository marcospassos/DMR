<?php

namespace DMR\Functional;

use DMR\Mapping\SimpleReader;

/**
 * ODM functional tests.
 *
 * @author Marcos Passos <marcos@marcospassos.com>
 */
class SimpleReaderODMTest extends AbstractReaderTest
{
	public function getReader()
	{
		$driver = $this->getMockMongoDBMappingDriver();

		return new SimpleReader($this->getMockMongoDBDocumentManager($driver));
	}
	
	public function getNamespaced($class)
	{
		return 'DMR\Functional\Fixture\Model\ODM\\' . $class;
	}
}
