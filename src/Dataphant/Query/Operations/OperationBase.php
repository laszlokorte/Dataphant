<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Operations;

use Dataphant\Query\Operations\Exceptions\InsufficientOperandsException;

use Dataphant\Query\Operations\OrOperation;
use Dataphant\Query\Operations\AndOperation;
use Dataphant\Query\Operations\NotOperation;

use Dataphant\Query\ConditionBase;

use ArrayIterator;

abstract class OperationBase extends ConditionBase implements OperationInterface
{
	/**
	 * The operations children operations|comparisons
	 *
	 * @var array
	 */
	protected $operands = array();

	/**
	 * The parent operation if available
	 *
	 * @var Operation
	 */
	protected $parent = NULL;

	/**
	 * the operations slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * internal counter for iteration
	 *
	 * @var int
	 */
	protected $interationKey = 0;

	/**
	 * applies a new operation on a list of other operations|comparisons
	 *
	 * @param string $operands
	 */
	public function __construct($operands = array())
	{
		foreach($operands AS $op)
		{
			$this->addOperand($op);
		}

		parent::__construct();
	}

	public function getSlug()
	{
		return $this->slug;
	}

	public function getOperands()
	{
		return $this->operands;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
	}


	public function isValid()
	{
		if(count($this->operands) > 1)
		{
			foreach($this->operands AS $operand)
			{
				if( ! $operand->isValid())
				{
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}


	public function merge($operands)
	{
		foreach($operands AS $op)
		{
			$this->addOperand($op);
		}
		return $this;
	}

	public function count()
	{
		return count($this->operands);
	}

	public function getIterator()
	{
		return new ArrayIterator($this->operands);
	}

	public function __clone()
	{
		foreach($this->operands AS $key => $op)
		{

			if($this->operands[$key] instanceof OperationInterface)
			{
				$this->operands[$key] = clone $op;
				$this->operands[$key]->setParent($this);
			}
		}
	}

	protected function addOperand($operand)
	{
		if($operand instanceof OperationInterface)
		{
			$operand = clone $operand;
			$operand->setParent($this);
		}
		$this->operands[] = $operand;
	}
}
