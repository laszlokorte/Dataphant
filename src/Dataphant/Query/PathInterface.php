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

use IteratorAggregate;
use Dataphant\ComparableInterface;
use Dataphant\AggregatableInterface;
use Dataphant\SortableInterface;
/**
 * A Path contains a list of chained relations.
 * eg User -> Membership -> Squad -> Board -> Threads
 * It may contain a reference to the last relationship's target's attribute
 * eg News -> Comment -> visibility
 *
 * A Path is used to build join conditions
 */
interface PathInterface extends ComparableInterface, AggregatableInterface, SortableInterface, IteratorAggregate
{

	/*
		IteratorAggregate Interface:
		Allows iterating trough all path's relationships

		abstract public Traversable getIterator ( void )
	*/

	/**
	 * undocumented function
	 *
	 * @return Property the property the path points to
	 */
	public function getProperty();


	/**
	 * Get all the relationships the path contains.
	 *
	 * @return array
	 */
	public function getRelationships();


	/**
	 * Get the last relationship of the path's the relationships stack
	 *
	 * @return RelationshipInterface
	 */
	public function getLastRelationship();

}
