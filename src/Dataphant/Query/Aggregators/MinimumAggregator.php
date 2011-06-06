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
 * MinimumAggregator
 */
class MinimumAggregator extends AggregatorBase
{

	protected function calculateFor($collection)
	{
		$minimum = NULL;

		foreach($collection AS $record)
		{
			$value = $this->property->getValueFor($record);
			if($minimum === NULL || $minimum > $value)
			{
				$minimum = $value;
			}
		}

		return $minimum;
	}

	protected function typecast($value)
	{
		return (float)$value;
	}

	public function getAliasName()
	{
		return 'minimum_' . $this->property->getFieldName();
	}


}
