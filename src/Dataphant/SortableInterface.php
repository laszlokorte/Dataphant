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

/**
 * Aggregate methods for properties
 */
interface SortableInterface
{
	/**
	 * get an Order object to sort by a property
	 * ASC
	 */
	public function asc();

	/**
	 * get an Order object to sort by a property
	 * DESC
	 */
	public function desc();


}
