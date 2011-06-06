<?php

namespace Dataphant\Tests\Relationships;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Relationships\ManyToOneRelationship;

class ManyToOneRelationshipTest extends BaseTestCase
{

	public function setUp()
	{
		$this->relname = 'group';
		$userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$userModel::defineProperty('group_id', array('type' => 'Integer'));
		$this->userModel = $userModel;
		$this->groupModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
	}

	public function testSourceAndTargetGetSetOnInitialization()
	{
		$manyToOne = new ManyToOneRelationship($this->relname, $this->userModel, $this->groupModel);

		$this->assertSame($this->relname, $manyToOne->getName());
		$this->assertSame($this->userModel, $manyToOne->getSourceModel());
		$this->assertSame($this->groupModel, $manyToOne->getTargetModel());
	}

	public function testOptionsCanBeSetOnInitialization()
	{
		$options = array(
			'foreign_key' => 'fid'
		);

		$manyToMany = new ManyToOneRelationship($this->relname, $this->userModel, $this->groupModel, $options);

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
