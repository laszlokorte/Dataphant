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
 * Aggregate methods for properties.
 */
interface AggregatableInterface
{
	/**
	 * get an operation for calculating a properties average
	 */
	public function avg();

	/**
	 * get an operation for counting a properties occurrence
	 */
	public function count();

	/**
	 * get an operation for finding the number of minimum
	 */
	public function min();

	/**
	 * get an operation for finding the number of maximum
	 */
	public function max();

	/**
	 * get an operation for calculating a properties sum
	 */
	public function sum();

}
