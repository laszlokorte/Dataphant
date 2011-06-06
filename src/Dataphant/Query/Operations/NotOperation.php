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

use Dataphant\Query\ConditionInterface;

class NotOperation extends OperationBase
{

	protected $slug = 'not';


	public function __construct(ConditionInterface $child)
	{
		parent::__construct(array($child));
	}

	public function match($subject)
	{
		return ! end($this->operands)->match($subject);
	}

	public function isValid()
	{
		return count($this->operands) === 1 && end($this->operands)->isValid();
	}

	public function applyToRecord($record)
	{
		# Do nothing
	}
}
