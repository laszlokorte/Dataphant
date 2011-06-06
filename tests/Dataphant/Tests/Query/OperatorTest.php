<?php

namespace Dataphant\Tests\Query;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Query\Operator;

class OperatorTest extends BaseTestCase
{

	public function setUp()
	{
		$model = 'User';
		$propertyName = 'nickname';
		$this->property = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('phpunitbug'), array($model, $propertyName)
		);
	}


	public function testSlugAndPropertyAreSetOnInitialization()
	{
		$slug = 'count';
		$operator = new Operator($slug, $this->property);

		$this->assertSame($slug, $operator->getSlug());
		$this->assertSame($this->property, $operator->getProperty());
	}
}
