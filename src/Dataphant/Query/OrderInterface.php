<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query;

/**
 * An order object is used to sort queries
 */
interface OrderInterface
{

	/**
	 * Get the order's property
	 *
	 * @return PropertyInterface
	 */
	public function getProperty();


	/**
	 * The order's direction (asc or desc)
	 *
	 * @return string
	 */
	public function getDirection();


	/**
	 * Compare the two given records.
	 * This method is used as callback for usort().
	 * It returns either 1, 0 or -1 depending one which record is first in order.
	 *
	 * @param RecordInterface $recordOne
	 * @param RecordInteface $recordTwo
	 *
	 * @return integer
	 */
	public function compareRecords($recordOne, $recordTwo);


	/**
	 * Check if this Order object is the same as the other Order object.
	 *
	 * @param OrderInterface $otherOrder
	 *
	 * @return boolean
	 */
	public function isEqualTo($otherOrder);
}
