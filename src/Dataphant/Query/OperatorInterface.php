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
 * An operator wraps a property to apply a function on it
 * eg COUNT, AVG, ...
 */
interface OperatorInterface
{
	/**
	 * Retrieve the operators slug
	 *
	 * @return string the slug
	 */
	public function getSlug();

	/**
	 * Retrieve the operators property
	 *
	 * @return PropertyInterface the property
	 */
	public function getProperty();
}
