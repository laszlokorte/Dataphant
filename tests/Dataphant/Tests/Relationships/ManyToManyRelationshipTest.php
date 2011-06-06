<?php

namespace Dataphant\Tests\Relationships;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Relationships\ManyToManyRelationship;


class ManyToManyRelationshipTest extends BaseTestCase
{
	public function setUp()
	{
		$this->relname = 'comments';
		$this->userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->previlegModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
	}

	public function testSourceAndTargetGetSetOnInitialization()
	{
		$manyToMany = new ManyToManyRelationship($this->relname, $this->userModel, $this->previlegModel);

		$this->assertSame($this->relname, $manyToMany->getName());
		$this->assertSame($this->userModel, $manyToMany->getSourceModel());
		$this->assertSame($this->previlegModel, $manyToMany->getTargetModel());
	}

	public function testOptionsCanBeSetOnInitialization()
	{
		$options = array(
			'foreign_key' => 'fid'
		);

		$manyToMany = new ManyToManyRelationship($this->relname, $this->userModel, $this->previlegModel, $options);

		$o = $manyToMany->getOptions();

		foreach($options AS $option => $value)
		{
			$this->assertSame($value, $o[$option]);
		}

	}

	public function testInverseRelationGetBeGet()
	{
		$this->markTestIncomplete('Still not implemented.');
	}

}
