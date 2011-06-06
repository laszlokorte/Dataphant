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
 * A DataSource is a proxy for a Database adapter.
 * It's responsibility is to manage the identity maps for the datasource
 * and for converting the DataAdapters response into Model objects
 *
 */
interface DataSourceInterface
{

	/**
	 * Gets an datasource instance by the given name or create one
	 *
	 * @param string $name
	 * @return DataSourceInterface
	 */
	static public function getByName($name);


	/**
	 * Resets a DataSource by passing it's name
	 *
	 * @return void
	 */
	static public function resetByName($name);


	/**
	 * insert the given records into the database
	 *
	 * @param array $records The list of records to create
	 * @return integer the number of records has been created
	 */
	public function create($records);


	/**
	 * Selects a list of records from the DataSource
	 *
	 * @param QueryInterface $query The Query object to determine the conditions
	 * @param boolean $asArray Get the raw resultset instead of mapped objects
	 * @return CollectionInterface
	 */
	public function read($query, $asArray = FALSE);


	/**
	 * Updates a list of records in the DataSource
	 *
	 * @param CollectionInterface $query the Query object to determine the conditions
	 * @param CollectionInterface
	 * @return integer the number of records updated
	 */
	public function update($attributes, $collection);


	/**
	 * Deletes a list of records from the DataSource
	 *
	 * @param CollectionInterface the collection of records to delete
	 * @return integer The number of records deleted
	 */
	public function delete($collection);


	/**
	 * Select aggregated properties for the given Query.
	 *
	 * @param QueryInterface $query
	 *
	 * @return array Aggregation results
	 */
	public function aggregate($query);


	/**
	 * generate a new query object for querying the DataSource
	 *
	 * @param string $model The model to retrieve the result from
	 * @param string $options
	 * @return void
	 */
	public function getNewQuery($model, $options = array());


	/**
	 * get the identity map for a specific model in the datasource
	 *
	 * @param string $modelName the models name you want to get the identity map for
	 * @return IdentityMapInterface the IdentityMap
	 */
	public function getIdentityMap($modelName);


	/**
	 * Get the datasources database adapter
	 *
	 * @return AdapterInterface the database adapter
	 */
	public function getAdapter();


	/**
	 * get the datasource's name
	 *
	 * @return string the data'sources name
	 */
	public function getName();

}
