<?php

namespace Dataphant\Tests\Query\Operations;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Operations\AndOperation;
use Dataphant\Query\Operations\NullOperation;

class AndOperationTest extends QueryBaseTestCase
{
	public function testSlugIsNamedAnd()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();

		$operation = new AndOperation(array($cond1, $cond2));

		$this->assertSame('and', $operation->getSlug());
	}

	public function testCloningClonesAllChildOperations()
	{
		$cond1 = $this->getOperand(TRUE, TRUE, TRUE);
		$cond2 = $this->getOperand(TRUE, TRUE, TRUE);

		$operation = new AndOperation(array($cond1, $cond2));

		$clonedOperation = clone $operation;

		foreach($clonedOperation AS $cO)
		{
			foreach($operation AS $o)
			{
				$this->assertNotSame($o, $cO);
			}
		}

		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();

		$operation = new AndOperation(array($cond1, $cond2));

		$clonedOperation = clone $operation;

		$i = 0;
		foreach($clonedOperation AS $cO)
		{
			foreach($operation AS $o)
			{
				if($o === $cO)
				{
					$i++;
				}
			}
		}
		$this->assertSame(2, $i);
	}

	public function testOperandsParentGetResetOnClone()
	{
		$cond1 = new NullOperation();
		$cond2 = new NullOperation();

		$operation = new AndOperation(array($cond1, $cond2));

		$clonedOperation = clone $operation;

		foreach($clonedOperation AS $cO)
		{
			$this->assertSame($clonedOperation, $cO->getParent());
			$this->assertNotSame($operation, $cO->getParent());
		}
	}

	public function testOperandsParentGetSetOnInitialization()
	{
		$cond1 = new NullOperation();
		$cond2 = new NullOperation();

		$operation = new AndOperation(array($cond1, $cond2));

		foreach($operation AS $op)
		{
			$this->assertSame($operation, $op->getParent());
		}
	}

	public function testAtLeastTwoOperandsHaveToBePassedToBeValid()
	{
		$cond = $this->getOperand();

		$operation = new AndOperation(array($cond));
		$this->assertFalse($operation->isValid());

		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new AndOperation($operands);
		$this->assertSame($operands, $operation->getOperands());
		$this->assertTrue($operation->isValid());
	}

	public function testValidityDependsOnOperandsVadility()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();

		$operation = new AndOperation(array($cond1, $cond2));

		$this->assertTrue($operation->isValid());


		$cond1 = $this->getOperand(TRUE, FALSE);
		$cond2 = $this->getOperand();

		$operation = new AndOperation(array($cond1, $cond2));

		$this->assertFalse($operation->isValid());
	}

	public function testOperandsCanBeCounted()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new AndOperation($operands);

		$this->assertSame(2, count($operation));
	}

	public function testOperandsCanBeIterated()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new AndOperation($operands);

		$i = 0;
		foreach($operation AS $operand)
		{
			$this->assertSame($operands[$i], $operand);
			$i++;
		}
		$this->assertSame(count($operands), $i);
	}

	public function testRecordMatchesOperationIfItMatchesAllOperands()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');

		$operands = array($this->getOperand(TRUE), $this->getOperand(TRUE));

		$operation = new AndOperation($operands);

		$this->assertTrue($operation->match($record));
	}

	public function testRecordDoesNotMatchOperationIfItDoesNotMatchAllOperands()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');

		$operands = array($this->getOperand(TRUE), $this->getOperand(FALSE));

		$operation = new AndOperation($operands);

		$this->assertFalse($operation->match($record));
	}

	public function testRecordDoesNotMatchOperationIfItMatchesNoOperands()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');

		$operands = array($this->getOperand(FALSE), $this->getOperand(FALSE));

		$operation = new AndOperation($operands);

		$this->assertFalse($operation->match($record));
	}

	protected function getOperand($matchReturn = TRUE, $valid = TRUE, $operation = FALSE)
	{
		if($operation !== FALSE)
		{
			$operand = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		}
		else
		{
			$operand = $this->getMock('Dataphant\\Query\\ConditionInterface');
		}
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
		$op = new AndOperation();
		$this->assertNull($op->getParent());
	}

	public function testParentCanBeSetAndGet()
	{
		$parent = $this->getMock('Dataphant\\Query\\ConditionInterface');
		$and = new AndOperation();

		$and->setParent($parent);
		$this->assertSame($parent, $and->getParent());
	}

	public function testCanBeMergedWithOtherOperation()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$and = new AndOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newAnd = $and->merge($other);
		$this->assertSame($and, $newAnd);

		$i = 0;

		foreach($newAnd AS $x)
		{
			$i++;
			$this->assertTrue(in_array($x, $comparisons, TRUE));
		}
		$this->assertSame(count($comparisons), $i);
	}

	public function testLogicalOrCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$and = new AndOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newAnd = $and->or_($other);
		$this->assertNotSame($and, $newAnd);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\OrOperation', $newAnd);
	}

	public function testCopyGetMergedIntoOrOperationWhenBeingComposedWithThroughtLogicalOr()
	{
		$this->markTestIncomplete('Not yet implemented.');
	}

	public function testLogicalAndCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$and = new AndOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newAnd = $and->and_($other);
		$this->assertNotSame($and, $newAnd);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $newAnd);
	}

	public function testLogicalAndComposingWithOtherOperationWillJustMergeCopies()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$and = new AndOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));

		$newAnd = $and->and_($other);
		foreach($newAnd AS $op)
		{
			$this->assertNotSame($and, $op);
			$this->assertNotSame($other, $op);
		}
	}

	public function testLogicalAndNotCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$and = new AndOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newAnd = $and->andNot_($other);
		$this->assertNotSame($and, $newAnd);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $newAnd);
	}
}
