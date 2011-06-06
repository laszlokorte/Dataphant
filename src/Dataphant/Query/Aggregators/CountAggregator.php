<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Aggregators;

/*
 * CountAggregator
 */
class CountAggregator extends AggregatorBase
{

	public function __construct($property = NULL)
	{
		$this->property = $property;
	}


	public function getAliasName()
	{
		if($this->property !== NULL)
		{
			return 'count_' . $this->property->getFieldName();
		}
		else
		{
			return 'count';
		}
	}


	protected function calculateFor($collection)
	{
		$counter = 0;
		foreach($collection AS $record)
		{
			if( ! $this->property || $this->property->getValueFor($record) !== NULL)
			{
				$counter++;
			}
		}

		return $counter;
	}


	protected function typecast($value)
	{
		return (int)$value;
	}
}
