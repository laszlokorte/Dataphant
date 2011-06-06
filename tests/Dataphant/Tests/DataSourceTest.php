<?php

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;

use Dataphant\DataSource;
use Dataphant\AdapterRegistry;

class DataSourceTest extends BaseTestCase
{

	public function setUp()
	{
		$this->registry = AdapterRegistry::getInstance();
	}

	public function tearDown()
	{
		AdapterRegistry::clearInstance();
	}

	public function testDefaultNameIsDefined()
	{
		$this->assertNotEmpty(DataSource::DEFAULT_NAME);
	}

	public function testThereIsOnlyOneInstancePerName()
	{
		$name = 'default';
		$sourceOne = DataSource::getByName($name);
		$sourceTwo = DataSource::getByName($name);

		$this->assertSame($sourceOne, $sourceTwo);

		DataSource::resetByName($name);
	}

	public function testDataSourceHasAName()
	{
		$name = 'default';

		$dataSource = DataSource::getByName($name);

		$this->assertSame($name, $dataSource->getName());

		DataSource::resetByName($name);
	}

	public function testDataSourceKnowsItsAdapterIfRegistered()
	{
		$name = 'default';

		$adapter = $this->getMockForAbstractClass('Dataphant\Adapters\AdapterBase', array($name));
		$adapter->expects($this->any())
		        ->method('getName')
		        ->will($this->returnValue($name));

		$this->registry->registerAdapter($adapter);

		$dataSource = DataSource::getByName($name);

		$this->assertSame($adapter, $dataSource->getAdapter());


		DataSource::resetByName($name);
	}

	public function testAdapterDoesNotNeedToBeRegisteredForInitialization()
	{
		$name = 'unknownAapter';
		$dataSource = DataSource::getByName($name);

		DataSource::resetByName($name);
	}

	public function testAdapterNeedsToBeRegisteredToBeAccessed()
	{
		$this->setExpectedException('Dataphant\\Exceptions\\AdapterNotFoundException');

		$name = 'unknownAapter';
		$dataSource = DataSource::getByName($name);
		$dataSource->getAdapter();

		DataSource::resetByName($name);
	}

	public function testIdentityMapCanBeAccessed()
	{
		$dataSource = DataSource::getByName('default');
		$identityMap = $dataSource->getIdentityMap('user');

		$this->assertInstanceOf('Dataphant\IdentityMapInterface', $identityMap);

		DataSource::resetByName('default');
	}

	public function testIdentityMapIsUniquePerModel()
	{
		$model = $this->getMockClass('Dataphant\ModelBase', array(uniqid('method')));

		$dataSource = DataSource::getByName('default');
		$firstIdentityMap = $dataSource->getIdentityMap($model);

		$this->assertSame($firstIdentityMap, $dataSource->getIdentityMap($model));

		DataSource::resetByName('default');
	}

	public function testQueryCanBeFactored()
	{
		$name = 'default';
		$adapter = $this->getMockForAbstractClass('Dataphant\Adapters\AdapterBase', array($name));
		$adapter->expects($this->any())
		        ->method('getName')
		        ->will($this->returnValue($name));

		$this->registry->registerAdapter($adapter);

		$dataSource = DataSource::getByName($name);
		$model = $this->getMockClass('Dataphant\ModelBase', array(uniqid('method')));
		$options = array();

		$query = $dataSource->getNewQuery($model, $options = array());

		$this->assertInstanceOf('Dataphant\Query\Query', $query);

		DataSource::resetByName($name);
	}

	public function testAnonymBindingsGetQuotedOnStatemenExecution()
	{
		$this->markTestIncomplete('Not yet implemented');
	}

	public function testNamedBindingsGetQuotedOnStatemenExecution()
	{
		$this->markTestIncomplete('Not yet implemented');
	}

	public function testDataSourceCanBeReseted()
	{
		$ds1 = DataSource::getByName('default');
		DataSource::resetByName('default');
		$ds2 = DataSource::getByName('default');

		$this->assertNotSame($ds1, $ds2);
	}

}
