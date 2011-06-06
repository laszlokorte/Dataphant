<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query;

use Dataphant\Query\Operations\AndOperation;
use Dataphant\Query\Operations\OrOperation;
use Dataphant\Query\Operations\NotOperation;
use Dataphant\Query\Operations\NullOperation;

/**
 * Providing the logical linking methods for both comparisons and operations
 */
abstract class ConditionBase implements ConditionInterface
{

	public function __construct()
	{

	}

	public function or_($otherOperation)
	{
		if($otherOperation instanceof NullOperation)
		{
			return $otherOperation;
		}
		return new OrOperation(array($this, $otherOperation));
	}

	public function and_($otherOperation)
	{
		if($otherOperation instanceof NullOperation)
		{
			return $this;
		}
		return new AndOperation(array($this, $otherOperation));
	}

	public function andNot_($otherOperation)
	{
		return new AndOperation(array($this, new NotOperation($otherOperation)));
	}

}
