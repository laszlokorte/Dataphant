<?php

namespace Dataphant\Tests\States;

use Dataphant\Tests\BaseTestCase;

use Dataphant\States\ImmutableState;

class ImmutableStateTest extends BaseTestCase
{
	public function setUp()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->record = $modelClass::build();
	}

	public function testPropertiesCanNotBeSet()
	{
		$this->setExpectedException('Dataphant\\States\\Exceptions\\ImmutableException');

		$titleProperty = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array('Article', 'title')
		);

		$immutableState = new ImmutableState($this->record);

		$immutableState->set($titleProperty, 'newTitle');
	}

	public function testPropertiesCanNotBeReadIfNotAlreadyLoaded()
	{
		$this->setExpectedException('Dataphant\\States\\Exceptions\\ImmutableException');

		// mocking an not yet loaded property
		$descriptionProperty = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array('Article', 'body')
		);
		$descriptionProperty->expects($this->any())
		                    ->method('isLoadedFor')
		                    ->will($this->returnValue(FALSE));

		$immutableState = new ImmutableState($this->record);

		$immutableState->get($descriptionProperty);
	}

	public function testPropertiesCanStillBeReadIfAlreadyLoaded()
	{
		$title = 'My little pony';
		// mocking an already loaded property
		$titleProperty = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array('Article', 'title')
		);
		$titleProperty->expects($this->any())
		                    ->method('isLoadedFor')
		                    ->will($this->returnValue(TRUE));
		$titleProperty->expects($this->any())
		                    ->method('getValueFor')
		                    ->will($this->returnValue($title));

		$immutableState = new ImmutableState($this->record);

		$this->assertSame($title, $immutableState->get($titleProperty));
	}

	public function testRecordCanNotBeDeleted()
	{
		$this->setExpectedException('Dataphant\\States\\Exceptions\\ImmutableException');

		$immutableState = new ImmutableState($this->record);

		$immutableState->delete();
	}

	public function testCommitDoesNotChangeTheState()
	{
		$immutableState = new ImmutableState($this->record);

		$this->assertSame($immutableState, $immutableState->commit());
	}

	public function testRollbackDoesNotChangeTheState()
	{
		$immutableState = new ImmutableState($this->record);

		$this->assertSame($immutableState, $immutableState->rollback());
	}
}
