<?php

namespace Dataphant\Tests\Query\Operations;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Operations\NullOperation;

class NullOperationTest extends QueryBaseTestCase
{
	public function testOperandsDoNotHaveToBePassendOnInitialization()
	{
		$operation = new NullOperation();
	}

	public function testAnyRecordIsMatched()
	{
		$anyRecord = $this->getMock('Dataphant\\RecordInterface');
		$operation = new NullOperation();

		$this->assertTrue($operation->match($anyRecord));
	}

	public function testSlugIsNamedNull()
	{
		$operation = new NullOperation();

		$this->assertSame('null', $operation->getSlug());
	}

	public function testCanNotHaveAnyOperand()
	{
		$operand = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$operation = new NullOperation(array($operand));
		$this->assertSame(0, count($operation));
	}

	public function testIsValidWithoutOperand()
	{
		$operation = new NullOperation();
		$this->assertTrue($operation->isValid());
	}

	public function testIteratorIsEmpty()
	{
		$nullOperation = new NullOperation();

		$j = 0;
		foreach($nullOperation AS $i)
		{
			$j++;
		}

		$this->assertSame(0, $j);
	}

	public function testParentIsNullByDefault()
	{
		$op = new NullOperation();
		$this->assertNull($op->getParent());
	}

	public function testParentCanBeSetAndGet()
	{
		$parent = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$null = new NullOperation();

		$null->setParent($parent);
		$this->assertSame($parent, $null->getParent());
	}

	public function testCanNotBeMergedWithOtherOperation()
	{
		$this->markTestIncomplete('Still not implemented.');
	}

	public function testComposingLogicalOrReturnsNullOperation()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$null = new NullOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$this->assertSame($null, $null->or_($other));
	}

	public function testComposingLogicalAndWithOtherOperationWillReturnOtherOperation()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$null = new NullOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$this->assertSame($other, $null->and_($other));
	}

	public function testComposingLogicalAndNotWithOtherOperationWillReturnNegationOfOtherOperation()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$null = new NullOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$new = $null->andNot_($other);

		$this->assertInstanceOf('Dataphant\\Query\\Operations\\NotOperation', $new);
	}
}
