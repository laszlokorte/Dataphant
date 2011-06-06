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
 * MaximumAggregator
 */
class MaximumAggregator extends AggregatorBase
{

	protected function calculateFor($collection)
	{
		$maximum = NULL;

		foreach($collection AS $record)
		{
			$value = $this->property->getValueFor($record);
			if($maximum === NULL || $maximum < $value)
			{
				$maximum = $value;
			}
		}

		return $maximum;
	}

	protected function typecast($value)
	{
		return (float)$value;
	}
	
	public function getAliasName()
	{
		return 'maximum_' . $this->property->getFieldName();
	}

}
