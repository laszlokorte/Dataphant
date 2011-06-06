<?php

namespace Dataphant\Tests\States;

use Dataphant\Tests\BaseTestCase;

use Dataphant\AdapterRegistry;
use Dataphant\Adapters\SqliteAdapter;
use Dataphant\States\TransientState;

class TransientStateTest extends BaseTestCase
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

	public function testDeleteDoesNotChangeTheState()
	{
		$transientState = new TransientState($this->record);

		$this->assertSame($transientState, $transientState->delete());
	}

	public function testRollbackDoesNotChangeTheState()
	{
		$transientState = new TransientState($this->record);

		$this->assertSame($transientState, $transientState->rollback());
	}

	public function testCommitChangesStateToClean()
	{
		$transientState = new TransientState($this->record);

		$newState = $transientState->commit();
		$this->assertInstanceOf('Dataphant\\States\\CleanState', $newState);
	}

	public function testPropertyCanBeSet()
	{
		$nickname = 'John Locke';
		$state = new TransientState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('setValueFor'), array('User', 'nickname')
		);
		$propertyNickname->expects($this->once())
		              ->method('setValueFor')
		              ->with($this->equalTo($this->record), $this->equalTo($nickname));


		$state->set($propertyNickname, $nickname);
	}

	public function testPropertyCanBeRead()
	{
		$nickname = 'John Locke';
		$state = new TransientState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('getValueFor'), array('User', 'nickname')
		);

		$propertyNickname->expects($this->any())
						->method('getValueFor')
						->will($this->returnValue($nickname));

		$this->assertSame($nickname, $state->get($propertyNickname));
	}

	public function testPropertysChangesGetTracked()
	{

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('getName'), array(get_class($this->record), 'nickname')
		);

		$propertyNickname->expects($this->any())
						->method('getName')
						->will($this->returnValue('age'));


		$dirtyState = new TransientState($this->record);
		$dirtyState->set($propertyNickname, 'newNickname');

		$this->assertSame(array($propertyNickname->getName() => NULL), $dirtyState->getOriginalAttributes());
	}

}
