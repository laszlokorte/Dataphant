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

class OrOperation extends OperationBase
{

	protected $slug = 'or';

	public function match($subject)
	{
		foreach($this->operands AS $operand)
		{
			if($operand->match($subject))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Make the record to match the first operand of the or operation
	 *
	 * @param RecordInterface $record
	 *
	 * @return void
	 */
	public function applyToRecord($record)
	{
		$this->operands[0]->applyToRecord($record);
	}
}
