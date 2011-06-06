<?php

namespace Dataphant\Tests\States;

use Dataphant\Tests\BaseTestCase;

use Dataphant\AdapterRegistry;
use Dataphant\Adapters\SqliteAdapter;
use Dataphant\States\DirtyState;

class DirtyStateTest extends BaseTestCase
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

	public function testRollbackChangesStateToClean()
	{
		$dirtyState = new DirtyState($this->record);
		$newState = $dirtyState->rollback();

		$this->assertInstanceOf('Dataphant\\States\\CleanState', $newState);
	}


	public function testDeleteChangesStateToDeleted()
	{
		$dirtyState = new DirtyState($this->record);
		$newState = $dirtyState->delete();

		$this->assertInstanceOf('Dataphant\\States\\DeletedState', $newState);
	}


	public function testCommitChangesStateToClean()
	{
		$dirtyState = new DirtyState($this->record);
		$newState = $dirtyState->commit();

		$this->assertInstanceOf('Dataphant\\States\\CleanState', $newState);
	}

	public function testPropertysChangesGetTracked()
	{

		$propertyNickname = $this->getPropertyMockWithRealDataSource('default', 'nickname', array('getValueFor'));

		$oldValue = 'oldNickname';
		$propertyNickname->expects($this->any())
		                 ->method('getValueFor')
		                 ->will($this->returnValue($oldValue));

		$dirtyState = new DirtyState($this->record);
		$dirtyState->set($propertyNickname, 'newNickname');

		$this->assertSame(array($propertyNickname->getName() => $oldValue), $dirtyState->getOriginalAttributes());
	}


	public function testSettingAllChangedPropertiesBackToTheirOldValuesChangesStateToClean()
	{
		$oldValue = 'oldValue';

		$property = $this->getPropertyMockWithRealDataSource('default', 'nickname', array('getValueFor'));
		$property->expects($this->any())
		         ->method('getValueFor')
		         ->will($this->returnValue($oldValue));

		$dirtyState = new DirtyState($this->record);

		$this->assertSame($dirtyState, $dirtyState->set($property, 'newValue'));

		$this->assertInstanceOf('Dataphant\\States\\CleanState', $dirtyState->set($property, $oldValue));
	}

	public function testPropertyCanBeRead()
	{
		$nickname = 'John Locke';
		$state = new DirtyState($this->record);

		$propertyNickname = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('getValueFor'), array('User', 'nickname')
		);

		$propertyNickname->expects($this->any())
						->method('getValueFor')
						->will($this->returnValue($nickname));

		$this->assertSame($nickname, $state->get($propertyNickname));
	}

	protected function getPropertyMockWithRealDataSource($dataSourceName, $propertyName, $methodsToMock = array())
	{
		// Generates a dataSource mock to allow the property class accessing it's name
		$dataSourceMock = $this->getMock('Dataphant\\DataSourceInterface');
		$dataSourceMock->expects($this->any())
		              ->method('getName')
		              ->will($this->returnValue($dataSourceName));


		// generating a mock child class of ModelBase to allow the property to access it's datasource through it
		$modelMock = $this->getMockClass('\Dataphant\\ModelBase', array('getDataSource'));
		$modelMock::staticExpects($this->any())
		              ->method('getDataSource')
		              ->will($this->returnValue($dataSourceMock));


		return $this->getMock(
			'Dataphant\Properties\PropertyBase',
			array_merge(array('phpunitbug'), $methodsToMock),
			array($modelMock, $propertyName)
		);
	}

}
