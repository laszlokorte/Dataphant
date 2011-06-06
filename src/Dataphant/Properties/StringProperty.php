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

/**
 * Property being a single text field
 */
class StringProperty extends PropertyBase
{
	static protected $defaultOptions = array(
		'unique' => FALSE,
		'lazy' => FALSE,
		'default' => NULL,
		'length' => 50
	);
}
