<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Comparisons;

use Dataphant\Query\ConditionInterface;

/**
 * Methods for creating a comparison betweet a subject and a value
 */
interface ComparisonInterface extends ConditionInterface
{

	/**
	 * get the subject to be compared with the value
	 * The subject can either be a path or a property
	 *
	 * @return string
	 */
	public function getSubject();


	/**
	 * get the value the Subject is compared with
	 *
	 * @return string
	 */
	public function getValue();


	/**
	 * Check if the comparison's subject is a relationship.
	 *
	 * @return void
	 */
	public function isComparingRelationship();
}
