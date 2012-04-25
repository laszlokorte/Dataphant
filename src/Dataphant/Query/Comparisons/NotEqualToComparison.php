<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Comparisons;

class NotEqualToComparison extends ComparisonBase
{
	public function match($record)
	{
		return $this->getGivenValueFor($record) != $this->getNeededValueFor($record);
	}

}
