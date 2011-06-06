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

class AndOperation extends OperationBase
{

	protected $slug = 'and';


	public function match($subject)
	{
		foreach($this->operands AS $operand)
		{
			if( ! $operand->match($subject))
			{
				return FALSE;
			}
		}
		return TRUE;
	}


	public function applyToRecord($record)
	{
		foreach($this->operands AS $operand)
		{
			$operand->applyToRecord($record);
		}
	}

}
