<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant;

use ArrayAccess;

/**
 * An identity map is used to store objects already fetched from the database to cache the row->object converting process
 * and to ensure there is only one instace of each database row
 */
interface IdentityMapInterface extends ArrayAccess
{
	/*
		Inherited from ArrayAccess:

		abstract public boolean offsetExists ( mixed $offset )
		abstract public mixed offsetGet ( mixed $offset )
		abstract public void offsetSet ( mixed $offset , mixed $value )
		abstract public void offsetUnset ( mixed $offset )

	*/
}
