<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Tests;

use PHPUnit_Framework_TestCase;

class BaseTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * Get a objects propertie's value
	 *
	 * @param string $obj
	 * @param string $attrName
	 * @return mixed
	 */
	public function getReflectionAttribute($obj, $attrName)
	{
		$reflObject = new \ReflectionObject($obj);
		$reflProperty = $reflObject->getProperty($attrName);

		$old = $reflProperty->isPublic();

		$reflProperty->setAccessible(TRUE);
		$value = $reflProperty->getValue($obj);
		$reflProperty->setAccessible($old);

		return $value;
	}
}
