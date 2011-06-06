<?php

namespace Dataphant\Tests\States;

use Dataphant\Tests\BaseTestCase;

use Dataphant\States\CleanState;

class CleanStateTest extends BaseTestCase
{

	public function setUp()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->record = $modelClass::build();
	}

	public function testStateChangesToDirtyWhenPropertyGetsSet()
	{
		$cleanstate = new CleanState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array(get_class($this->record), 'nickname')
		);
		$propertyNickname->expects($this->any())
		                       ->method('isLoadedFor')
		                       ->will($this->returnValue(TRUE));
		$propertyNickname->expects($this->any())
		                       ->method('getValueFor')
		                       ->will($this->returnValue('OldNick'));

		$newState = $cleanstate->set($propertyNickname, 'NewNick');
		$this->assertInstanceOf('Dataphant\\States\\DirtyState', $newState);
	}

	public function testPropertyCanBeRead()
	{
		$nickname = 'John Locke';
		$state = new CleanState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('getValueFor'), array('User', 'nickname')
		);

		$propertyNickname->expects($this->any())
						->method('getValueFor')
						->will($this->returnValue($nickname));

		$this->assertSame($nickname, $state->get($propertyNickname));
	}

	public function testStateChangesToDeletedOnDelete()
	{
		$cleanstate = new CleanState($this->record);

		$newState = $cleanstate->delete();

		$this->assertInstanceOf('Dataphant\\States\\DeletedState', $newState);
	}

	public function testStateStaysCleanWhenPropertyValueGetSetButDoesNotChange()
	{
		$cleanstate = new CleanState($this->record);

		$propertyEmail = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array('User', 'email')
		);
		$propertyEmail->expects($this->any())
		                    ->method('isLoadedFor')
		                    ->will($this->returnValue(TRUE));
		$propertyEmail->expects($this->any())
		                    ->method('getValueFor')
		                    ->will($this->returnValue('old@mail.com'));

		$newState = $cleanstate->set($propertyEmail, 'old@mail.com');
		$this->assertInstanceOf('Dataphant\\States\\CleanState', $newState);
	}

	public function testRollbackDoesNotChangeTheState()
	{
		$cleanstate = new CleanState($this->record);

		$this->assertSame($cleanstate, $cleanstate->rollback());
	}

	public function testCommitDoesDoesNotChangeTheState()
	{
		$cleanstate = new CleanState($this->record);

		$this->assertSame($cleanstate, $cleanstate->commit());
	}

	public function testListOfChangedPropertiesIsEmpty()
	{
		$cleanstate = new CleanState($this->record);

		$this->assertEmpty($cleanstate->getOriginalAttributes());
	}

}
