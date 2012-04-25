<?php

namespace Dataphant\Tests\Query\Comparisons;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Comparisons\NotEqualToComparison;
use Dataphant\Query\Operations;

class NotEqualToComparisonTest extends QueryBaseTestCase
{
	public function testRecordsPropertyMatchesNumericValueIfEqual()
	{
		$compareValue = 23;
		$realValue = 23;
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new NotEqualToComparison($this->property, $compareValue);

		$this->assertFalse($comparison->match($this->record));
	}

	public function testRecordsPropertyMatchesStringValueIfEqual()
	{
		$compareValue = 'FailWhale';
		$realValue = 'FailWhale';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new NotEqualToComparison($this->property, $compareValue);

		$this->assertFalse($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchNumericValueIfGreater()
	{
		$compareValue = 23;
		$realValue = 42;
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new NotEqualToComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchNumericValueIfLess()
	{
		$compareValue = 8;
		$realValue = 4;
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new NotEqualToComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchStringValueIfNotEqual()
	{
		$compareValue = 'RapidRaptor';
		$realValue = 'FailWhale';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new NotEqualToComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}


	public function testIsValidByDefault()
	{
		$comparison = new NotEqualToComparison($this->property, 16);
		$this->assertTrue($comparison->isValid());
	}

	public function testLogicalAndCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new NotEqualToComparison('3', '4');
		$op = new Operations\AndOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' <> '4') AND ('3' <> '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalOrCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new NotEqualToComparison('3', '4');
		$op = new Operations\OrOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' <> '4') OR ('3' <> '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalAndNotCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new NotEqualToComparison('3', '4');
		$notop = new Operations\NotOperation($comp);
		$op = new Operations\AndOperation(array($comp, $notop));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' <> '4') AND (NOT ('3' <> '4')))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}
}
