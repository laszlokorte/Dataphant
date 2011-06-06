<?php

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Collection;

class CollectionTest extends BaseTestCase
{

	public function testQueryGetsSetOnInitialization()
	{
		$dataSource = $this->getMock('Dataphant\\DataSourceInterface', array(), array('default'));
		$model = $this->getMockClass('Dataphant\ModelBase', array(uniqid('method')));
		$query = $this->getMock('Dataphant\\Query\\QueryInterface');
		$query->expects($this->any())
		      ->method('getModel')
		      ->will($this->returnValue($model));

		$collection = new Collection($model, $query);

		$this->assertSame($query, $collection->getQuery());
	}

	public function testFilteringACollectionsRecordsReturnsANewOne()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$nullOp = $this->getMock('Dataphant\\Query\\Operations\\NullOperation');
		$query = $this->getQuery($model);

		$condition = $this->getMock('Dataphant\\Query\\ConditionInterface');

		$collection = new Collection($model, $query);
		$newCollection = $collection->filter($condition);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testLimitingACollectionsRecordsReturnsANewOne()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);
		$limit = 20;

		$collection = new Collection($model, $query);
		$newCollection = $collection->limit($limit);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testSkippingACollectionsRecordsReturnsANewCollection()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);
		$offset = 20;

		$collection = new Collection($model, $query);
		$newCollection = $collection->skip($offset);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testOrderingACollectionsRecordsReturnsANewOne()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);
		$order = $this->getMock('Dataphant\\Query\\OrderInterface');

		$collection = new Collection($model, $query);
		$newCollection = $collection->orderBy($order);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testMakingACollectionToBeOutOfUniqueRecordsReturnsANewOne()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);

		$collection = new Collection($model, $query);
		$newCollection = $collection->uniq();

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testGettingAllCollectionsRecordsReturnsTheSameCollectionIfOptionsAreEmpty()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);

		$collection = new Collection($model, $query);
		$newCollection = $collection->all();

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertSame($collection, $newCollection);
		$this->assertSame($query, $newCollection->getQuery());
	}

	public function testGettingAllCollectionsRecordsReturnsANewCollectionIfOptionsAreGiven()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);

		$collection = new Collection($model, $query);
		$newCollection = $collection->all(array('limit' => 0));

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	public function testTheFirstCollectionsRecordCanBeAccessed()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);
		$record = $this->getMock('Dataphant\\RecordInterface');

		$collection = new Collection($model, $query, array($record));

		$this->assertSame($record, $collection->first());
	}

	public function testFieldsCanBeSetToBeEagerLoaded()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('description', array('lazy' => TRUE));

		$query = $this->getQuery($model);
		$eager = array('description');
		$collection = new Collection($model, $query);
		$newCollection = $collection->eagerLoad($eager);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());

		$fields = $newCollection->getQuery()->getFields();
		$this->assertSame(4, count($fields));
		$this->assertContains($model::description(), $fields);
	}

	public function testFieldsCanBeSetToBeLazyLoaded()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('description', array('lazy' => TRUE));

		$query = $this->getQuery($model);
		$lazy = array('email');

		$collection = new Collection($model, $query);
		$newCollection = $collection->lazyLoad($lazy);

		$this->assertInstanceOf(get_class($collection), $newCollection);
		$this->assertNotSame($collection, $newCollection);
		$this->assertNotSame($query, $newCollection->getQuery());



		$fields = $newCollection->getQuery()->getFields();

		$this->assertSame(2, count($fields));
		$this->assertNotContains($model::description(), $fields);
		$this->assertNotContains($model::email(), $fields);
		$this->assertContains($model::nickname(), $fields);
	}

	public function testQueryIsClonedWhenCollectionIsCloned()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$query = $this->getQuery($model);
		$collection = new Collection($model, $query);

		$newCollection = clone $collection;
		$this->assertNotSame($query, $newCollection->getQuery());
	}

	protected function getQuery($model)
	{
		$dataSource = \Dataphant\DataSource::getByName(uniqid('DataSource'));
		return  new \Dataphant\Query\Query($dataSource, $model);
	}
}
