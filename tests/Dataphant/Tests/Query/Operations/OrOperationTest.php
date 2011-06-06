<?php

namespace Dataphant\Tests\Query\Operations;

use Dataphant\Tests\Query\QueryBaseTestCase;

use Dataphant\Query\Operations\OrOperation;
use Dataphant\Query\Operations\NullOperation;

class OrOperationTest extends QueryBaseTestCase
{
	public function testSlugIsNamedOr()
	{
		$operation = new OrOperation();

		$this->assertSame('or', $operation->getSlug());
	}

	public function testCloningClonesAllChildOperations()
	{
		$cond1 = $this->getOperand(TRUE, TRUE, TRUE);
		$cond2 = $this->getOperand(TRUE, TRUE, TRUE);

		$operation = new OrOperation(array($cond1, $cond2));

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

		$operation = new OrOperation(array($cond1, $cond2));

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

		$operation = new OrOperation(array($cond1, $cond2));

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

		$operation = new OrOperation(array($cond1, $cond2));

		foreach($operation AS $op)
		{
			$this->assertSame($operation, $op->getParent());
		}
	}

	public function testOperandsCanBePassedOnInitialization()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();

		$operation = new OrOperation(array($cond1, $cond2));
	}

	public function testOperandsDoNotHaveToBePassendOnInitialization()
	{
		$operation = new OrOperation();
	}

	public function testAtLeastTwoChildrenHaveToBePassedToBeValid()
	{
		$cond = $this->getOperand();

		$operation = new OrOperation(array($cond));
		$this->assertFalse($operation->isValid());

		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new OrOperation($operands);
		$this->assertSame($operands, $operation->getOperands());
		$this->assertTrue($operation->isValid());
	}

	public function testValidityDependsOnOperandsVadility()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();

		$operation = new OrOperation(array($cond1, $cond2));

		$this->assertTrue($operation->isValid());


		$cond1 = $this->getOperand(TRUE, FALSE);
		$cond2 = $this->getOperand();

		$operation = new OrOperation(array($cond1, $cond2));

		$this->assertFalse($operation->isValid());
	}

	public function testOperandsCanBeCounted()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new OrOperation($operands);

		$this->assertSame(2, count($operation));
	}

	public function testOperandsCanBeIterated()
	{
		$cond1 = $this->getOperand();
		$cond2 = $this->getOperand();
		$operands = array($cond1, $cond2);

		$operation = new OrOperation($operands);

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

		$operation = new OrOperation($operands);

		$this->assertTrue($operation->match($record));
	}

	public function testRecordMatchesOperationIfItDoesMatchAnyOperand()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');

		$operands = array($this->getOperand(TRUE), $this->getOperand(FALSE));

		$operation = new OrOperation($operands);

		$this->assertTrue($operation->match($record));
	}

	public function testRecordDoesNotMatchOperationIfItMatchesNoOperands()
	{
		$record = $this->getMock('Dataphant\\RecordInterface');

		$operands = array($this->getOperand(FALSE), $this->getOperand(FALSE));

		$operation = new OrOperation($operands);

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
		$op = new OrOperation();
		$this->assertNull($op->getParent());
	}

	public function testParentCanBeSetAndGet()
	{
		$parent = new OrOperation();
		$or = new OrOperation();

		$or->setParent($parent);
		$this->assertSame($parent, $or->getParent());
	}

	public function testCanBeMergedWithOtherOperation()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$or = new OrOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newOr = $or->merge($other);
		$this->assertSame($or, $newOr);

		$i = 0;

		foreach($newOr AS $x)
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

		$or = new OrOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newOr = $or->or_($other);
		$this->assertNotSame($or, $newOr);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\OrOperation', $newOr);

	}

	public function testComposingLogicalAndWithOtherOperationWillJustMergeCopies()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$or = new OrOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));

		$newAnd = $or->and_($other);
		foreach($newAnd AS $op)
		{
			$this->assertNotSame($or, $op);
			$this->assertNotSame($other, $op);
		}
	}

	public function testLogicalAndCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$or = new OrOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newOr = $or->and_($other);
		$this->assertNotSame($or, $newOr);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $newOr);

	}

	public function testCopyGetMergedIntoAndOperationWhenBeingComposedWithThroughLogicalAnd()
	{
		$this->markTestIncomplete('Not yet implemented.');
	}

	public function testLogicalAndNotCompositionWithOtherOperationCanBeBuilt()
	{
		$comparisons = array(
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface'),
			$this->getMock('Dataphant\\Query\\Comparisons\\ComparisonInterface')
		);

		$or = new OrOperation();
		$other = $this->getMock('Dataphant\\Query\\Operations\\OperationInterface', array('setParent'));
		$other->expects($this->any())
		             ->method('getIterator')
		             ->will($this->returnValue(new \ArrayIterator($comparisons)));

		$newOr = $or->andNot_($other);
		$this->assertNotSame($or, $newOr);
		$this->assertInstanceOf('Dataphant\\Query\\Operations\\AndOperation', $newOr);

	}

}
