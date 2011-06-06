<?php

namespace Dataphant\Tests\Relationships;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Relationships\OneToManyRelationship;


class OneToManyRelationshipTest extends BaseTestCase
{
	public function setUp()
	{
		$this->relname = 'comments';
		$this->userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->commentModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
	}

	public function testSourceAndTargetGetSetOnInitialization()
	{
		$oneToMany = new OneToManyRelationship($this->relname, $this->userModel, $this->commentModel);

		$this->assertSame($this->relname, $oneToMany->getName());
		$this->assertSame($this->userModel, $oneToMany->getSourceModel());
		$this->assertSame($this->commentModel, $oneToMany->getTargetModel());
	}

	public function testOptionsCanBeSetOnInitialization()
	{
		$options = array(
			'foreign_key' => 'fid'
		);

		$manyToMany = new OneToManyRelationship($this->relname, $this->userModel, $this->commentModel, $options);

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
