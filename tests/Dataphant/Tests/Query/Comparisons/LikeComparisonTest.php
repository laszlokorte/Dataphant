<?php

namespace Dataphant\Tests\Query\Comparisons;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Comparisons\LikeComparison;
use Dataphant\Query\Operations;

class LikeComparisonTest extends QueryBaseTestCase
{
	public function testRecordsPropertyMatchesLikeExpressionWithMultiCharWildcardIfSuitable()
	{
		$compareValue = 'Lo%';
		$realValue = 'Locke';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new LikeComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyMatchesLikeExpressionWithSingleCharWildcardIfSuitable()
	{
		$compareValue = 'E_o';
		$realValue = 'Eko';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new LikeComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyMatchesLikeExpressionWithEscapeSequenceIfSuitable()
	{
		$compareValue = 'NameWith\\_Underscore%';
		$realValue = 'NameWith_UnderscoreAndMore';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new LikeComparison($this->property, $compareValue);

		$this->assertTrue($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchLikeExpressionIfNotSuitable()
	{
		$compareValue = 'Jacob%';
		$realValue = 'Esau';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new LikeComparison($this->property, $compareValue);

		$this->assertFalse($comparison->match($this->record));
	}

	public function testRecordsPropertyDoesNotMatchLikeExpressionWithEscapeSequenceIfNotSuitable()
	{
		$compareValue = 'NameWith\\_Underscore%';
		$realValue = 'NameWith-UnderscoreAndMore';
		$this->property->expects($this->any())
		             ->method('getValueFor')
		             ->will($this->returnValue($realValue));

		$comparison = new LikeComparison($this->property, $compareValue);

		$this->assertFalse($comparison->match($this->record));
	}

	public function testIsValidByDefault()
	{
		$comparison = new LikeComparison($this->property, 'John%');
		$this->assertTrue($comparison->isValid());
	}

	public function testLogicalAndCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new LikeComparison('3', '4');
		$op = new Operations\AndOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' LIKE '4') AND ('3' LIKE '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalOrCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new LikeComparison('3', '4');
		$op = new Operations\OrOperation(array($comp, $comp));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' LIKE '4') OR ('3' LIKE '4'))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}

	public function testLogicalAndNotCompositionWithOtherConditionCanBeBuilt()
	{
		$model = $this->getFakeModel();
		$query = $this->adapter->getNewQuery($this->dataSource, $model);
		$comp = new LikeComparison('3', '4');
		$notop = new Operations\NotOperation($comp);
		$op = new Operations\AndOperation(array($comp, $notop));
		$query->setConditions($op);

		$this->adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\" FROM \"users\" ";
		$sql .= "WHERE (('3' LIKE '4') AND (NOT ('3' LIKE '4')))";
		$this->assertSame($sql, $this->adapter->getLastStatement());
	}
}
