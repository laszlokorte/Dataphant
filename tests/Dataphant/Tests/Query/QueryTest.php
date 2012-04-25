<?php

namespace Dataphant\Tests\Query;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Query\Query;
use Dataphant\DataSource;

class QueryTest extends BaseTestCase
{

	public function setUp()
	{
		$this->dataSource = DataSource::getByName('default');
		$this->model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
	}

	public function tearDown()
	{
		DataSource::resetByName('default');
	}

	public function testDataSourceAndModelGetSetOnIntialization()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertSame($this->dataSource, $query->getDataSource());
		$this->assertSame($this->model, $query->getModel());
	}

	public function testLinksAreEmptyArrayInitialy()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertTrue(is_array($query->getLinks()));
		$this->assertEmpty($query->getLinks());
	}

	public function testOffsetIsZeroInitialy()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertSame(0, $query->getOffset());
	}

	public function testLimitIsNullInitialy()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertNull($query->getLimit());
	}

	public function testOrderIsEmptyArrayInitialy()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertTrue(is_array($query->getOrder()));
		$this->assertEmpty($query->getOrder());
	}

	public function testConditionIsTrueInitialy()
	{
		$query = new Query($this->dataSource, $this->model);

		$this->assertInstanceOf('Dataphant\\Query\\Operations\\NullOperation', $query->getConditions());
	}

	public function testUniquenessIsDisabledInitialy()
	{
		$query = new Query($this->dataSource, $this->model);
		$this->assertFalse($query->toBeUnique());
	}

	public function testReloadIsDisabledInitialy()
	{
		$query = new Query($this->dataSource, $this->model);
		$this->assertFalse($query->toBeReloaded());
	}

	public function testOptionsCanBeSetOnInitialization()
	{
		$options = array('offset' => 20, 'limit' => 30);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame($options['offset'], $query->getOffset());
		$this->assertSame($options['limit'], $query->getLimit());
	}

	public function testOffsetCanBeSetOnInitialization()
	{
		$options = array('offset' => 20);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame(20, $query->getOffset());
	}

	public function testOffsetHaveToBeAnInteger()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidOffsetException');

		$options = array('offset' => 'foobar');
		$query = new Query($this->dataSource, $this->model, $options);
	}

	public function testLimitCanBeSetOnInitialization()
	{
		$options = array('limit' => 20);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame(20, $query->getLimit());
	}

	public function testLimitHaveToBeAnInteger()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidLimitException');

		$options = array('limit' => 'foobar');
		$query = new Query($this->dataSource, $this->model, $options);
	}

	public function testLinksCanBeSetOnInitialization()
	{
		$links = array($this->getMock('Dataphant\\Relationships\\RelationshipInterface'));
		$options = array('links' => $links);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame($links, $query->getLinks());
	}

	public function testLinksHaveToBeRelationships()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidRelationshipException');

		$options = array('links' => array('foobar'));
		$query = new Query($this->dataSource, $this->model, $options);
	}

	public function testMultipleOrdersCanBeSetOnInitialization()
	{
		$orderOne = $this->getMock('Dataphant\\Query\\OrderInterface');
		$orderTwo = $this->getMock('Dataphant\\Query\\OrderInterface');

		$options = array('order' => array($orderOne, $orderTwo));
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame(array($orderOne, $orderTwo), $query->getOrder());
	}

	public function testOrderHaveToBeAnInstanceOfOrderInterface()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidOrderException');

		$options = array('order' => array('foobar'));
		$query = new Query($this->dataSource, $this->model, $options);
	}

	public function testConditionCanBeSetOnInitialization()
	{
		$condition = $this->getMock('Dataphant\\Query\\ConditionInterface');

		$options = array('conditions' => $condition);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame($condition, $query->getConditions());
	}

	public function testConditionCanBeCleared()
	{
		$condition = $this->getMock('Dataphant\\Query\\ConditionInterface');

		$options = array('conditions' => $condition);
		$query = new Query($this->dataSource, $this->model, $options);

		$this->assertSame($condition, $query->getConditions());

		$query2 = $query->clearConditions();

		$this->assertSame($query, $query2);

		$this->assertInstanceOf('Dataphant\\Query\\Operations\\NullOperation', $query->getConditions());
	}

	public function testUniqnessCanBeSetOnInitialization()
	{
		$options = array('unique' => FALSE);
		$query = new Query($this->dataSource, $this->model, $options);
		$this->assertFalse($query->toBeUnique());

		$options = array('unique' => TRUE);
		$query = new Query($this->dataSource, $this->model, $options);
		$this->assertTrue($query->toBeUnique());
	}

	public function testReloadCanBeEnabledOnInitialization()
	{
		$options = array('reload' => FALSE);
		$query = new Query($this->dataSource, $this->model, $options);
		$this->assertFalse($query->toBeReloaded());

		$options = array('reload' => TRUE);
		$query = new Query($this->dataSource, $this->model, $options);
		$this->assertTrue($query->toBeReloaded());
	}

	public function testQueriesFieldsAreAllModelsNotLazyFieldsByDefault()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('name');
		$model::defineProperty('age');
		$model::defineProperty('description', array('lazy' => TRUE));
		$properties = $model::getProperties();

		$query = new Query($this->dataSource, $model);
		$fields = $query->getFields();

		$this->assertSame($properties['name'], $fields['name']);
		$this->assertSame($properties['age'], $fields['age']);
		$this->assertSame(3, count($fields));
		$this->assertFalse(isset($fields['description']));
	}


	public function testQueriesFieldsCanBeSetByTheirName()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('name');
		$model::defineProperty('age');
		$model::defineProperty('description');
		$properties = $model::getProperties();

		$query = new Query($this->dataSource, $model);
		$fields = $query->setFields(array('name'));
		$fields = $query->getFields();

		$this->assertSame($properties['name'], $fields['name']);
		$this->assertSame(1, count($fields));
		$this->assertFalse(isset($fields['age']));
		$this->assertFalse(isset($fields['description']));
	}


	public function testQueriesFieldsCanBeSetByPropertyObjects()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('name');
		$model::defineProperty('age');
		$model::defineProperty('description');
		$properties = $model::getProperties();

		$query = new Query($this->dataSource, $model);
		$fields = $query->setFields(array($model::name(), $model::age()));
		$fields = $query->getFields();

		$this->assertSame($properties['name'], $fields['name']);
		$this->assertSame($properties['age'], $fields['age']);
		$this->assertSame(2, count($fields));
		$this->assertFalse(isset($fields['description']));
	}

	public function testUnknownOptionsCanNotBeSetViaArrayAccess()
	{
		$this->setExpectedException('InvalidArgumentException');
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$query = new Query($this->dataSource, $model);
		$query['foobar'] = 'Blub';
	}

	public function testOptionsCanBeSetViaArrayAccess()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$query = new Query($this->dataSource, $model);
		$query['limit'] = 35;

		$this->assertSame(35, $query->getLimit());
	}

	public function testOptionsGetBeGetViaArrayAccess()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('name');
		$model::defineProperty('age');
		$model::defineProperty('description');
		$properties = $model::getProperties();

		$query = new Query($this->dataSource, $model);
		$fields = array($model::name(), $model::age());

		$query->setFields($fields);

		$this->assertTrue(isset($query['fields']));
		$this->assertFalse(isset($query['foobar']));
		$this->assertSame($query->getFields(), $query['fields']);
	}

	public function testNotExistingOptionsCanNotBeRead()
	{
		$this->setExpectedException('InvalidArgumentException');
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$query = new Query($this->dataSource, $model);
		$query['foobar'];
	}

	public function testOptionsCanNotBeUnsetViaArrayAccess()
	{
		$model =  $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('name');
		$model::defineProperty('age');
		$model::defineProperty('description');
		$properties = $model::getProperties();

		$query = new Query($this->dataSource, $model);
		$fields = array($model::name(), $model::age());

		$this->setExpectedException('BadMethodCallException');
		unset($query['fields']);
	}


}
