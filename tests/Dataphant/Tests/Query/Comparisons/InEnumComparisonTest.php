<?php

namespace Dataphant\Tests\Query\Comparisons;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Comparisons\InEnumComparison;
use Dataphant\Query\Operations;

class InEnumComparisonTest extends QueryBaseTestCase
{
	public function testRecordsPropertyMatchesArrayIfIncluded()
	{
		$this->property->expects($this->any())
		               ->method('getValueFor')
		               ->will($this->returnValue('admin'));
		$comparison = new InEnumComparison($this->property, array('admin', 'webmaster'));

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchArrayIfNotIncluded()
	{
		$this->property->expects($this->any())
		               ->method('getValueFor')
		               ->will($this->returnValue('member'));
		$comparison = new InEnumComparison($this->property, array('admin', 'webmaster'));

		$this->assertFalse($comparison->match($this->record));
	}

	public function testIsValidByDefault()
	{
		$comparison = new InEnumComparison($this->property, array('a', 'b', 'c'));
		$this->assertTrue($comparison->isValid());
	}

	public function testLogicalAndCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new InEnumComparison(3, array('a', 'b', 'c'));
		$op = new Operations\AndOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE ((3 IN ('a', 'b', 'c')) AND (3 IN ('a', 'b', 'c')))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalOrCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new InEnumComparison(3, array('a', 'b', 'c'));
		$op = new Operations\OrOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE ((3 IN ('a', 'b', 'c')) OR (3 IN ('a', 'b', 'c')))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalAndNotCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new InEnumComparison(3, array('a', 'b', 'c'));
		$notop = new Operations\NotOperation($comp);
		$op = new Operations\AndOperation(array($comp, $notop));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE ((3 IN ('a', 'b', 'c')) AND (NOT (3 IN ('a', 'b', 'c'))))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}
}
