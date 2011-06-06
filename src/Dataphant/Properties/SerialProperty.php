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

use Dataphant\Properties\Integer;

/**
 * Property being a unique autoincrementing integer
 */
class SerialProperty extends IntegerProperty
{

	static protected $forcedOptions = array(
		'serial' => TRUE,
		'unique' => TRUE,
		'key' => TRUE
	);

}
