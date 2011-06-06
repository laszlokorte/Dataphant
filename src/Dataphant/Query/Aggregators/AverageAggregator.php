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
 * AverageAggregator
 */
class AverageAggregator extends AggregatorBase
{

	protected function calculateFor($collection)
	{
		$count = count($collection);

		if($count < 1)
		{
			return NULL;
		}

		$sum = 0;
		foreach($collection AS $record)
		{
			$sum += $this->property->getValueFor($record);
		}

		return $sum/$count;
	}

	protected function typecast($value)
	{
		return (float)$value;
	}

	public function getAliasName()
	{
		return 'average_' . $this->property->getFieldName();
	}

}
