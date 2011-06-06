<?php

namespace Dataphant\Tests\Relationships;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Relationships\OneToOneRelationship;


class OneToOneRelationshipTest extends BaseTestCase
{
	public function setUp()
	{
		$this->relname = 'comments';
		$this->userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->profileModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
	}

	public function testSourceAndTargetGetSetOnInitialization()
	{
		$oneToOne = new OneToOneRelationship($this->relname, $this->userModel, $this->profileModel);

		$this->assertSame($this->relname, $oneToOne->getName());
		$this->assertSame($this->userModel, $oneToOne->getSourceModel());
		$this->assertSame($this->profileModel, $oneToOne->getTargetModel());
	}

	public function testOptionsCanBeSetOnInitialization()
	{
		$options = array(
			'foreign_key' => 'fid'
		);

		$oneToOne = new OneToOneRelationship($this->relname, $this->userModel, $this->profileModel, $options);

		$o = $oneToOne->getOptions();

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
