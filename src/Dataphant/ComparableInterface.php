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
 * methods for comparing an object with another
 */
interface ComparableInterface
{

	/**
	 * Equals
	 *
	 * @param mixed $value the value to compare with.
	 * @return EqualToComparison A comparison object containing the comparable object and the given value.
	 */
	public function eq($value);

	/**
	 * Greater than
	 *
	 * @param integer $value the value to compare with
	 * @return GreaterThanComparison A comparison object containing the comparable object and the given value.
	 */
	public function gt($value);

	/**
	 * Greater than or Equals
	 *
	 * @param integer $value the value to compare with
	 * @return GreaterThanEqualToComparison A comparison object containing the comparable object and the given value.
	 */
	public function gte($value);

	/**
	 * Lower than
	 *
	 * @param integer $value the value to compare with
	 * @return LowerThanComparison A comparison object containing the comparable object and the given value.
	 */
	public function lt($value);

	/**
	 * Lower than or equals
	 *
	 * @param integer $value the value to compare with
	 * @return LowerThanEqualToComparison A comparison object containing the comparable object and the given value.
	 */
	public function lte($value);

	/**
	 * like
	 *
	 * You may use _ and % as placeholder for one or many chars.
	 *
	 * @param string $value the value to compare with
	 * @return LikeComparison A comparison object containing the comparable object and the given value.
	 */
	public function like($value);


	/**
	 * in
	 *
	 * @param array $value
	 * @return InEnumComparison A comparison object containing the comparable object and the given value.
	 */
	public function in($value);

}
