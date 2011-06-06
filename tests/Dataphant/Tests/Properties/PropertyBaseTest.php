<?php

namespace Dataphant\Tests\Properties;

use Dataphant\Tests\BaseTestCase;
use Dataphant\AdapterRegistry;
use Dataphant\Adapters\SqliteAdapter;

class PropertyBaseTest extends BaseTestCase
{

	public function testModelAndNameGetSetOnInitialization()
	{
		$model = 'User';
		$propertyName = 'nickname';
		$property = $this->getPropertyMock($model, $propertyName);

		$this->assertSame($model, $property->getModel());
		$this->assertSame($propertyName, $property->getName());
	}

	public function testNameIsFieldnameByDefault()
	{
		$model = 'User';
		$propertyName = 'nickname';
		$property = $this->getPropertyMock($model, $propertyName);

		$this->assertSame($propertyName, $property->getFieldName());
	}

	public function testFieldNameCanBeSetViaOptions()
	{
		$fieldName = 'excerp';

		$property =$this->getPropertyMock('Article', 'introduction', array('fieldname' => $fieldName));
		$this->assertSame($fieldName, $property->getFieldName());
	}

	public function testUniquenessIsDisabledByDefault()
	{
		$property = $this->getPropertyMock('User', 'nickname');

		$this->assertSame(FALSE, $property->isUnique());
	}

	public function testUniquenessCanBeEnabledViaOptions()
	{
		$unq = TRUE;

		$property =$this->getPropertyMock('Article', 'introduction', array('unique' => $unq));
		$this->assertSame($unq, $property->isUnique());
	}

	public function testLazyLoadingIsDisabledByDefault()
	{
		$property = $this->getPropertyMock('User', 'nickname');
		$this->assertSame(FALSE, $property->isLazy());
	}

	public function testLazyLoadingCanBeEnabledViaOptions()
	{
		$lazy = TRUE;

		$property =$this->getPropertyMock('Article', 'introduction', array('lazy' => $lazy));
		$this->assertSame($lazy, $property->isLazy());
	}

	public function testDefaultValueIsNullByDefault()
	{
		$record = $this->getMock('Dataphant\RecordInterface', array('phpunitbug'));
		$property = $this->getPropertyMock('User', 'nickname');
		$this->assertSame(NULL, $property->getDefaultValueFor($record));
	}

	public function testDefaultValueCanBeSetViaOptions()
	{
		$record = $this->getMock('Dataphant\RecordInterface', array('phpunitbug'));

		$defaultTitle = 'untitled document';

		$property =$this->getPropertyMock('Article', 'title', array('default' => $defaultTitle));
		$this->assertSame($defaultTitle, $property->getDefaultValueFor($record));
	}

	public function testConvertableIntoAscendingOrder()
	{
		$property = $this->getPropertyMock('User', 'nickname');

		$order = $property->asc();
		$this->assertInstanceOf('Dataphant\Query\OrderInterface', $order);
		$this->assertSame($property, $order->getProperty());
		$this->assertSame('asc', $order->getDirection());
	}

	public function testConvertableIntoDescendingOrder()
	{
		$property = $this->getPropertyMock('User', 'nickname');

		$order = $property->desc();
		$this->assertInstanceOf('Dataphant\Query\OrderInterface', $order);
		$this->assertSame($property, $order->getProperty());
		$this->assertSame('desc', $order->getDirection());
	}

	public function testCountAggregatorCanBeApplied()
	{
		$property = $this->getPropertyMock('User', 'id');

		$aggregator = $property->count();
		$this->assertInstanceOf('Dataphant\\Query\\Aggregators\\CountAggregator', $aggregator);
		$this->assertSame($property, $aggregator->getProperty());
	}

	public function testAverageAggregatorCanBeApplied()
	{
		$property = $this->getPropertyMock('User', 'id');

		$aggregator = $property->avg();
		$this->assertInstanceOf('Dataphant\\Query\\Aggregators\\AverageAggregator', $aggregator);
		$this->assertSame($property, $aggregator->getProperty());
	}

	public function testMinimumAggregatorCanBeApplied()
	{
		$property = $this->getPropertyMock('User', 'id');

		$aggregator = $property->min();
		$this->assertInstanceOf('Dataphant\\Query\\Aggregators\\MinimumAggregator', $aggregator);
		$this->assertSame($property, $aggregator->getProperty());
	}

	public function testMaximumAggregatorCanBeApplied()
	{
		$property = $this->getPropertyMock('User', 'id');

		$aggregator = $property->max();
		$this->assertInstanceOf('Dataphant\\Query\\Aggregators\\MaximumAggregator', $aggregator);
		$this->assertSame($property, $aggregator->getProperty());
	}

	public function testSumAggregatorCanBeApplied()
	{
		$property = $this->getPropertyMock('User', 'id');

		$aggregator = $property->sum();
		$this->assertInstanceOf('Dataphant\\Query\\Aggregators\\SumAggregator', $aggregator);
		$this->assertSame($property, $aggregator->getProperty());
	}

	public function testEqualityComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'password');

		$value = 'geheim';
		$operator = $property->eq($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\EqualToComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}

	public function testGreaterThanComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'age');

		$value = 16;
		$operator = $property->gt($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\GreaterThanComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}

	public function testForGreaterThanOrEqualityComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'age');

		$value = 4;
		$operator = $property->gte($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}

	public function testLessThanComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'age');

		$value = 42;
		$operator = $property->lt($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\LessThanComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}

	public function testLessThanOrEqualityComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'age');

		$value = 23;
		$operator = $property->lte($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\LessThanOrEqualToComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}

	public function testSimilarityComparisonCanBePerformed()
	{
		$property = $this->getPropertyMock('User', 'nickname');

		$value = 'Sni%';
		$operator = $property->like($value);
		$this->assertInstanceOf('Dataphant\Query\Comparisons\LikeComparison', $operator);
		$this->assertSame($property, $operator->getSubject());
		$this->assertSame($value, $operator->getValue());
	}


	public function testNullValueIsNotValidIfPropertyIsSetToNotBeNull()
	{
		$property = $this->getPropertyMock('User', 'nickname', array('required' => TRUE));

		$this->assertTrue($property->isRequired());
		$this->assertFalse($property->isValidValue(NULL));
	}

	public function testNullValueIsValidIfNotRequired()
	{
		$property = $this->getPropertyMock('User', 'nickname', array('required' => FALSE));

		$this->assertFalse($property->isRequired());
		$this->assertTrue($property->isValidValue(NULL));
	}

	public function testNoNullValueIsValIfRequired()
	{
		$property = $this->getPropertyMock('User', 'nickname', array('required' => TRUE));

		$this->assertTrue($property->isRequired());
		$this->assertTrue($property->isValidValue('Peter'));
	}

	public function testDataSourceCanBeAccessed()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		AdapterRegistry::getInstance()->registerAdapter(new SqliteAdapter('default'));
		$property = $this->getPropertyMock($model, 'nickname');

		$this->assertInstanceOf('Dataphant\\DataSource', $property->getDataSource());

		AdapterRegistry::clearInstance();
	}

	protected function getPropertyMock($model, $propertyName, $options = array())
	{
		/*
			FIXME
			although PropertyBase is an abstract class we must not use getMockForAbstractClass because
			phpunit does not like abstract classes which have no abstract methods

			Also we have to pass a non-empty array as second parameter because otherwise phpunit mocks ALL methods
			instead of no one
		*/
		return $this->getMock(
			'Dataphant\Properties\PropertyBase',
			array('phpunitbug'),
			array($model, $propertyName, $options)
		);
	}

}
