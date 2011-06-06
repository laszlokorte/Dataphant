<?php

namespace Dataphant\Tests\Query\Comparisons;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison;
use Dataphant\Query\Operations;

class GreaterThanOrEqualToComparisonTest extends QueryBaseTestCase
{
	public function testPropertyAndReferenceValueGetSetOnInitialization()
	{
		$age = 25;
		$comparison = new GreaterThanOrEqualToComparison($this->property, $age);

		$this->assertSame($this->property, $comparison->getSubject());
		$this->assertSame($age, $comparison->getValue());
	}

	public function testRecordsPropertyMatchesNumericIfGreater()
	{
		$age = 23;
		$this->property->expects($this->any())
		               ->method('getValueFor')
		               ->will($this->returnValue(42));

		$comparison = new GreaterThanOrEqualToComparison($this->property, $age);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyMatchesNumericIfEqual()
	{
		$age = 23;
		$this->property->expects($this->any())
		               ->method('getValueFor')
		               ->will($this->returnValue($age));

		$comparison = new GreaterThanOrEqualToComparison($this->property, $age);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchNumericIfLess()
	{
		$age = 23;
		$this->property->expects($this->any())
		               ->method('getValueFor')
		               ->will($this->returnValue(16));

		$comparison = new GreaterThanOrEqualToComparison($this->property, $age);

		$this->assertFalse($comparison->match($this->record));
	}

	public function testComparisonIsNotTypeStrict()
	{
		$compareValue = '8';
		$realValue = 16;
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new GreaterThanOrEqualToComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}


	public function testIsValidByDefault()
	{
		$comparison = new GreaterThanOrEqualToComparison($this->property, 16);
		$this->assertTrue($comparison->isValid());
	}

	public function testLogicalAndCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new GreaterThanOrEqualToComparison('3', '4');
		$op = new Operations\AndOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' >= '4') AND ('3' >= '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalOrCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new GreaterThanOrEqualToComparison('3', '4');
		$op = new Operations\OrOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' >= '4') OR ('3' >= '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalAndNotCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new GreaterThanOrEqualToComparison('3', '4');
		$notop = new Operations\NotOperation($comp);
		$op = new Operations\AndOperation(array($comp, $notop));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' >= '4') AND (NOT ('3' >= '4')))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}
}
