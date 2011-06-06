<?php

namespace Dataphant\Tests\States;

use Dataphant\Tests\BaseTestCase;

use Dataphant\AdapterRegistry;
use Dataphant\Adapters\SqliteAdapter;
use Dataphant\States\DeletedState;

class DeletedStateTest extends BaseTestCase
{
	public function setUp()
	{
		$adapterRegistry = AdapterRegistry::getInstance();
		$adapterRegistry->registerAdapter(new SqliteAdapter('default'));
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->record = $modelClass::build();
	}

	public function tearDown()
	{
		AdapterRegistry::clearInstance();
	}

	public function testDeleteAgainDoesNotChangeTheState()
	{
		$deletedState = new DeletedState($this->record);

		$this->assertSame($deletedState, $deletedState->delete());
	}

	public function testPropertiesCanNotBeSet()
	{
		$this->setExpectedException('Dataphant\\States\\Exceptions\\DeletedImmutableException');

		$propertyEmail = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('isLoadedFor', 'getValueFor'), array('User', 'email')
		);

		$deletedState = new DeletedState($this->record);

		$deletedState->set($propertyEmail, 'mynew@email.com');
	}

	public function testPropertyCanBeRead()
	{
		$nickname = 'John Locke';
		$state = new DeletedState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('getValueFor'), array('User', 'nickname')
		);

		$propertyNickname->expects($this->any())
						->method('getValueFor')
						->will($this->returnValue($nickname));

		$this->assertSame($nickname, $state->get($propertyNickname));
	}

	public function testStateChangesToImmutableWhenCommitted()
	{
		$deletedState = new DeletedState($this->record);
		$newstate = $deletedState->commit();

		$this->assertInstanceOf('Dataphant\\States\\ImmutableState', $newstate);
	}

	public function testNoPropertyChangesHaveBeenTracked()
	{
		$deletedState = new DeletedState($this->record);

		$this->assertEmpty($deletedState->getOriginalAttributes());
	}
}
