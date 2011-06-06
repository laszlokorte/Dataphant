<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Utils;

use Traversable;

/*
 * A collection of array functions
 */
class ArrayTools
{

	/**
	 * Convert the given value to a one dimensional array
	 *
	 * @param mixed $array
	 *
	 * @return array
	 */
	static public function flatten($array)
	{
		if(! is_array($array) && ! $array instanceof Traversable)
		{
			return array($array);
		}

		$result = array();

		foreach($array AS $a)
		{
			$b = static::flatten($a);
			foreach($b AS $c)
			{
				$result[] = $c;
			}
		}

		return $result;
	}

	static public function getOrDefault($array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}

}
