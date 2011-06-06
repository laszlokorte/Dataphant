<?php

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;

use Dataphant\AdapterRegistry;

class AdapterRegistryTest extends BaseTestCase
{


	public function tearDown()
	{
		AdapterRegistry::clearInstance();
	}

	public function testSingletonInstanceCanBeCleared()
	{
		$adapterRegistryOne = AdapterRegistry::getInstance();
		AdapterRegistry::clearInstance();
		$adapterRegistryTwo = AdapterRegistry::getInstance();

		$this->assertTrue($adapterRegistryOne !== $adapterRegistryTwo);
	}

	public function testSingletonPatternIsProvided()
	{
		$adapterRegistryOne = AdapterRegistry::getInstance();
		$adapterRegistryTwo = AdapterRegistry::getInstance();

		$this->assertSame($adapterRegistryOne, $adapterRegistryTwo);
	}


	public function testNoAdaptersAreRegisteredOnInitilization()
	{
		$adapterRegistry = AdapterRegistry::getInstance();
		$this->assertSame(array(), $adapterRegistry->getAllAdapters());
	}


	public function testAdapterCanBeRegistered()
	{
		$adapterRegistry = AdapterRegistry::getInstance();
		$adapterName = 'mockSql';
		$adapter = $this->getAdapterMock($adapterName);

		$adapterRegistry->registerAdapter($adapter);

		$this->assertSame($adapter, $adapterRegistry->getAdapter($adapterName));
	}


	public function testAllRegisteredAdaptersCanBeListed()
	{
		$adapterRegistry = AdapterRegistry::getInstance();

		$adapterName = 'mockSql';
		$adapter = $this->getAdapterMock($adapterName);

		$adapterRegistry->registerAdapter($adapter);

		$this->assertSame(array($adapterName => $adapter), $adapterRegistry->getAllAdapters());
	}


	public function testNonRegistedAdaptersCanNotBeAccessed()
	{

		$adapterRegistry = AdapterRegistry::getInstance();

		$this->setExpectedException('Dataphant\\Exceptions\\AdapterNotFoundException');

		$adapterRegistry->getAdapter('notRegistedAdapter');
	}

	protected function getAdapterMock($adapterName)
	{
		return new \Dataphant\Adapters\SqliteAdapter($adapterName);
	}

}
