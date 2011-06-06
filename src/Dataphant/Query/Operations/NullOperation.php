<?php

namespace Dataphant\Query\Operations;

use ArrayIterator;

/**
 * Empty condition matching all subjects
 */
class NullOperation extends OperationBase
{

	protected $slug = 'null';

	public function __construct()
	{
		// do nothing
	}

	public function isValid()
	{
		/**
		 * Has no operands, is always valid
		 */
		return TRUE;
	}

	public function match($subject)
	{
		# matches all subjects
		return TRUE;
	}

	public function or_($otherOperation)
	{
		return $this;
	}


	public function and_($otherOperation)
	{
		return $otherOperation;
	}


	public function andNot_($otherOperation)
	{
		return new NotOperation($otherOperation);
	}

	public function getIterator()
	{
		return new ArrayIterator(array());
	}

	public function count()
	{
		return 0;
	}

	public function applyToRecord($record)
	{
		# Do nothing
	}
}
