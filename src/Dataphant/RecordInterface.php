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
 * This inteface provides method for accessing database records
 * eg a User
 * By default it is implemented by ModelBase
 */
interface RecordInterface extends ArrayAccess
{
	/**
	 * Get a record's properties value
	 *
	 * @param string $propertyName
	 * @return mixed
	 */
	public function getAttribute($propertyName);

	/**
	 * Set a record's properties value
	 *
	 * @param string $propertyName
	 * @param string $propertyValue
	 * @return void
	 */
	public function setAttribute($propertyName, $propertyValue);

	/**
	 * Change multiple attributes by giving a associative array
	 *
	 * @param array $attributes
	 * @return array attributes which have been set
	 */
	public function setAttributes($attributes);

	/**
	 * Get a the associative array of all records attributes
	 *
	 * @return array
	 */
	public function getAttributes();


	/**
	 * Check if the value of the property with the given name is loaded for the record.
	 *
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isAttributeLoaded($propertyName);


	/**
	 * Check if the record has an attribute with the given name.
	 *
	 * @param string $attributeName
	 *
	 * @return Boolean
	 */
	public function hasAttribute($attributeName);


	/**
	 * Get all the properties the value has changed and not been saved.
	 * The returned array is keyed by the propertie's name and contains the attributes' values.
	 *
	 * @return array
	 */
	public function getDirtyAttributes();

	/**
	 * resets the record to the last persited state
	 *
	 * @return RecordInterface (fluent interface)
	 */
	public function reload();

	/**
	 * persist the record (incl parent an child records) into the database
	 *
	 * @return void
	 * @return boolean if the record has been saved successfuly
	 */
	public function save($runHooks = TRUE);

	/**
	 * destroys the record
	 *
	 * @param string $runHooks
	 * @return boolean if the record has been destroyd successfuly
	 */
	public function destroy($runHooks = TRUE);


	/**
	 * Get the records current state object
	 *
	 * @return StateInterface
	 */
	public function getState();


	/**
	 * Set the records new state
	 *
	 * @param $state StateInterface
	 */
	public function setState($state);


	/**
	 * Has this record never been saved to a database?
	 *
	 * @return boolean
	 */
	public function isNew();


	/**
	 * Has this record been destroyed?
	 *
	 * @return boolean
	 */
	public function isDestroyed();


	/**
	 * Has this record not been changed since fetched from database?
	 *
	 * @return boolean
	 */
	public function isClean();


	/**
	 * Has this record been destroyed?
	 *
	 * @return boolean
	 */
	public function isDirty();


	/**
	 * Can this record not be changed anymore?
	 *
	 * @return boolean
	 */
	public function isReadonly();


	/**
	 * The model's class name the record belongs to
	 *
	 * @return string
	 */
	public function getModel();


	/**
	 * Get the records key for the identiyMap
	 *
	 * @return string
	 */
	public function getKey();


	/**
	 * Set the collection the record should belong to.
	 *
	 * @param CollectionInterface $collection
	 * @return void
	 */
	public function setCollection($collection);

	/**
	 * Get the collection the record belongs to currently.
	 *
	 * @return CollectionInterface
	 */
	public function getCollection();

	/**
	 * Get a collection only this query is in.
	 *
	 * @return CollectionInterface
	 */
	public function getCollectionForSelf();

	/**
	 * Get a query which would select exactly this record again.
	 *
	 * @return QueryInterface
	 */
	public function getQueryForSelf();
}
