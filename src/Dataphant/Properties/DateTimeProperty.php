<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Properties;

use Dataphant\Properties\PropertyBase;

use DateTime;

/**
 * Property being a date/time field
 */
class DateTimeProperty extends PropertyBase
{

	public function serialize($value)
	{
		return $value->getTimestamp();
	}

	public function unserialize($value)
	{
		return new DateTime($value);
	}

	public function isValidValue($value)
	{
		return parent::isValidValue($value) && ($value instanceof DateTime || $value === NULL);
	}
}
