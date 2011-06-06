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

use IteratorAggregate;
use Countable;
use ArrayAccess;

/**
 * A collection is a group of records(database rows/instance of a Model class)
 *
 * These records do not have to be fetched already. Therefor the collection contains a Query object
 * which in turn contains the conditions to fetch the data
 *
 * The collection provides a fassade for accessing the query simplified
 * The collection provides methods to be iterated
 */
interface CollectionInterface extends IteratorAggregate, Countable, ArrayAccess
{
	/*
		Inherited from IteratorAggregate

		abstract public getIterator()
	*/

	/*
		Inherited from Countable

		abstract public int count ( void )
	*/

	/*
		Inherited from ArrayAccess

		offsetSet($offset) for accessing the collections records via array accesing
		* do not implement setter *
	*/


	/**
	 * Get a record of the collection by it's key
	 *
	 * @param mixed $key The record's key. When the record have multiple keys this is an array.
	 *
	 * @return RecordInterface The fetched record
	 */
	public function get($key);


	/**
	 * duplicates the collection updating the options and fetch all it's records, then return the first one.
	 * Throws an exception if the database returns more the one record.
	 *
	 * @param array $options
	 * @return void
	 */
	public function one($options);


	/**
	 * duplicates the collection setting the limit to 1 and setting the $options
	 *
	 * @param array $options
	 * @return RecordInterface the collections first record
	 */
	public function first($options);


	/**
	 * duplicates the collection with the set $options
	 *
	 * @param array $options
	 * @return void
	 */
	public function all($options);


	/**
	 * Filter the collection by a given condition.
	 * This duplicates the collection, modifies and returns the new one.
	 * sql: WHERE
	 *
	 * When the collection already contains conditions the old one will be linked via AND logic with the new
	 *
	 * @param array $conditions the conditions the collection should be filtered by
	 * @return CollectionInterface (fluent interface)
	 */
	public function filter($conditios);


	/**
	 * Set the fields to be loaded when the records are fetched from the database.
	 *
	 * @return void
	 */
	public function eagerLoad($fields);


	/**
	 * Set the fields which should not be loaded until they are needed.
	 *
	 * @return void
	 */
	public function lazyLoad($fields);


	/**
	 * Limit the collection by a given size.
	 * This duplicates the collection, modifies and returns the new one.
	 * sql: LIMIT
	 *
	 * @param integer $size
	 * @return CollectionInterface (fluent interface)
	 */
	public function limit($size);


	/**
	 * Add a $offset for the collection.
	 * This duplicates the collection, modifies and returns the new one.
	 * sql: OFFSET
	 *
	 * Important: This does not just SET the given $offset but adds it to the current one
	 *
	 * @param integer $size
	 * @return CollectionInterface (fluent interface)
	 */
	public function skip($offset);


	/**
	 * Sort the collection by a given order.
	 * This duplicates the collection, modifies and returns the new one.
	 * sql: ORDER BY
	 *
	 * @return CollectionInterface (fluent interface)
	 */
	public function orderBy($order);


	/**
	 * Make the collection to contain only one of multiple same records.
	 * This duplicates the collection, modifies and returns the new one.
	 * sql: DISTINCT
	 *
	 * @return CollectionInterface (fluent interface)
	 */
	public function uniq();


	/**
	 * Get the collection's query object
	 *
	 * @return QueryInterface
	 */
	public function getQuery();


	/**
	 * get the collection's datasource object
	 *
	 * @return DataSourceInterface
	 */
	public function getDataSource();


	/**
	 * get the collection's model's class name
	 *
	 * @return string the model's class name
	 */
	public function getModel();


	/**
	 * reload the collection's records from the datasource
	 *
	 * @return void
	 */
	public function reload();


	/**
	 * Clean the collection's record list and mark the collection as "not loaded".
	 * On the next access the records get refetched from the database.
	 *
	 * @return void
	 */
	public function reset();


	/**
	 * Forces the collection the fetch the database.
	 *
	 * @return void
	 */
	public function forceLoad();


	/**
	 * Fetch the records and return them as simple array.
	 *
	 * @return array
	 */
	public function getArray();


	/**
	 * Have the records already been fetched from the database
	 *
	 * @return boolean
	 */
	public function isLoaded();


	/**
	 * Save all the collection's records
	 *
	 * @return void
	 */
	public function save();


	/**
	 * Deletes all records in the collection.
	 *
	 * @return void
	 */
	public function destroy();


	/**
	 * Are all the collection's records clean?
	 *
	 * @return boolean
	 */
	public function isClean();


	/**
	 * Has any of the collection's record been changed?
	 *
	 * @return boolean
	 */
	public function isDirty();


	/**
	 * Set the collection's list of records to the given one
	 *
	 * @param array $records
	 *
	 * @return void
	 */
	public function setRecords($records);


	/**
	 * undocumented function
	 *
	 * @param array $records
	 *
	 * @return void
	 */
	public function replaceRecords($records);


	/**
	 * Removes the given record from the collection.
	 *
	 * @param RecordInterface $record
	 *
	 * @return RecordInterface The removed record.
	 */
	public function removeRecord($record);


	/**
	 * Add the given record to the collection.
	 *
	 * @param RecordInterface $record
	 *
	 * @return RecordInterface The added record.
	 */
	public function addRecord($record);


	/**
	 * Set a collection of records to be all the targets of the given
	 * relationships for all the collection's records.
	 *
	 * @param RelationshipInterface $relationship
	 * @param string $collection
	 *
	 * @return void
	 */
	public function setChildCollectionFor($relationship, $collection);


	/**
	 * Get the collection of all the target records for the given
	 * relationship of all the collection's records.
	 *
	 * @param RelationshipInterface $relationship
	 *
	 * @return CollectionInterface
	 */
	public function getChildCollectionFor($relationship);


	/**
	 * Check if a collection for the given relationship is available.
	 *
	 * @param RelationshipInterface $elationship
	 *
	 * @return boolean
	 */
	public function hasChildCollectionFor($relationship);


	/**
	 * Calculate
	 *
	 * @param string $aggregator
	 *
	 * @return void
	 */
	public function calculate($aggregator);

}
