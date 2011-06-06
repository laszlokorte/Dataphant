<?php

namespace Dataphant\Tests\Query\Operations;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Operations\NotOperation;

class NotOperationTest extends QueryBaseTestCase
{
	public function testSlugIsNamedNot()
	{
		$cond = $this->getMock('Dataphant\\Query\\ConditionInterface');

		$operation = new NotOperation($cond);

		$this->assertSame('not', $operation->getSlug());
	}

	public function testCloningClonesOperandWhenOperation()
	{
		$cond = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));

		$operation = new NotOperation($cond);
		$newNot = clone $operation;

		foreach($operation AS $op)
		{
			$this->assertNotSame($cond, $op);
		}
	}

	public function testOperandsParentGetSetOnAssigning()
	{
		$cond = $this->getMockForAbstractClass('Dataphant\\Query\\Operations\\OperationBase');

		$operation = new NotOperation($cond);

		foreach($operation AS $op)
		{
			$this->assertSame($operation, $op->getParent());
		}
	}

	public function testRecordMatchesIfItWouldNotForOperand()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');
		$operand = $this->getOperand(FALSE);

		$operation = new NotOperation($operand);
		$this->assertTrue($operation->match($record));
	}

	public function testRecordDoesNotMatchIfItWouldForOperand()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');
		$operand = $this->getOperand(TRUE);

		$operation = new NotOperation($operand);
		$this->assertFalse($operation->match($record));
	}

	public function testValidityDependsOnOperandsVadility()
	{
		$operand = $this->getOperand(TRUE, TRUE);
		$operation = new NotOperation($operand);

		$this->assertTrue($operation->isValid());

		$operand = $this->getOperand(TRUE, FALSE);
		$operation = new NotOperation($operand);

		$this->assertFalse($operation->isValid());
	}

	public function testCanNotHaveMoreThanOneOperand()
	{
		$this->setExpectedException('PHPUnit_Framework_Error');
		$operation = new NotOperation(array($this->getOperand(), $this->getOperand()));
	}

	protected function getOperand($matchReturn = TRUE, $valid = TRUE)
	{
		$operand = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$operand->expects($this->any())
		             ->method('match')
		             ->will($this->returnValue($matchReturn));

		$operand->expects($this->any())
		             ->method('isValid')
		             ->will($this->returnValue($valid));

		return $operand;
	}

	public function testParentIsNullByDefault()
	{
		$op = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$not = new NotOperation($op);
		$this->assertNull($not->getParent());
	}

	public function testParentCanBeSetAndGet()
	{
		$parent = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$op = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$not = new NotOperation($op);

		$not->setParent($parent);
		$this->assertSame($parent, $not->getParent());
	}

	public function testCanBeMergedWithOtherOperation()
	{
		$this->markTestIncomplete('Still not implemented.');
	}

	public function testLogicalOrCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$not = new NotOperation($this->getMockForAbstractClass('Dataphant\\Query\\Operations\\OperationBase'));
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$new = $not->or_($other);
		$this->assertNotSame($not, $new);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\OrOperation', $new);
	}

	public function testLogicalAndCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$not = new NotOperation($this->getMockForAbstractClass('Dataphant\\Query\\Operations\\OperationBase'));
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$new = $not->and_($other);
		$this->assertNotSame($not, $new);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $new);
	}

	public function testLogicalAndNotCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$not = new NotOperation($this->getMockForAbstractClass('Dataphant\\Query\\Operations\\OperationBase'));
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$new = $not->andNot_($other);
		$this->assertNotSame($not, $new);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $new);
	}
}
