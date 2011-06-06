<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Adapters;

/**
 * An AdapterInterface provides generalized methods for accessing a database.
 * A database adapter provides the db responses as arrays or integers.
 * the open database connection is hold by it's adapter
 */
interface AdapterInterface
{


	/**
	 * The the raw database connection the adapter uses.
	 *
	 * @return mixed The database connection depends on the used adapter. For sql adapters it is mostly an PDOobject.
	 */
	public function getConnection();


	/**
	 * Insert the given records into the database.
	 *
	 * @param array $records The list of records to create.
	 *
	 * @return integer The number of records which have been created.
	 */
	public function create($records);


	/**
	 * Selects a list of records from the database.
	 *
	 * @param object $query The query object to determine the conditions.
	 *
	 * @return array An array of associative array.
	 */
	public function read($query);


	/**
	 * Updates a list of records in the database.
	 *
	 * @param object $attributes The attributes to update.
	 * @param object $collection The collection of data to update.
	 *
	 * @return int the number of records updated
	 */
	public function update($attributes, $collection);


	/**
	 * Deletes a list of records from the database.
	 *
	 * @param CollectionInterface $collection The collection of records to delete.
	 *
	 * @return int The number of records deleted
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
	 * Creates the data structure needed to persist the given model.
	 *
	 * @param string $model The model's class name.
	 * @return void
	 */
	public function createDataSchema($model);


	/**
	 * Drop the datastructure the given model's records were persisted in.
	 *
	 * @param string $model The model's class name.
	 * @return void
	 */
	public function dropDataSchema($model);


	/**
	 * Generate a new query object for querying the databaseAdapter.
	 *
	 * Factory method for building a query object.
	 *
	 * @param string $dataSource The datasource to fetch the results from.
	 * @param string $model      The model to retrieve the result from.
	 * @param array  $options    Options to pass to the query.
	 *
	 * @return QueryInterface
	 */
	public function getNewQuery($dataSource, $model, $options=array());


	/**
	 * Get the adapters name.
	 *
	 * @return string The adapters name.
	 */
	public function getName();


	/**
	 * Toggle the adapters debug mode.
	 * When debugging is enabled the sql commands do not get flushed to to the datbase but just get logged.
	 *
	 * @param boolean $bool
	 * @return void
	 */
	public function setDebugMode($bool);


	/**
	 * Check if the debug mode is enabled.
	 *
	 * @return boolean If the debug mode is enabled.
	 */
	public function getDebugMode();


	/**
	 * Flush a statement to the database.
	 * The second parameter can be used to pass values to be used in the statement seperately to make them being escaped.
	 * For that you have to use placeholders inside the statement string to tell where each alue have to be inserted after
	 * being escaped.
	 *
	 * When $bindings is an numeric keyed array you can simply use questionmarks (?) inside the statement and they will be
	 * replaced which one value of the $bindings array each in the order the values are given by the $bindings array.
	 *
	 * $this->execute('SELECT * FROM users WHERE nickname = ? AND password = ?', array('sniper', 'secret'))
	 *
	 * will result in SELECT * from users WHERE nickname = 'sniper' and password = 'secret'
	 *
	 * Alternatively or if the $bindings array is associative you can use the array's keys prefixed with a colon as placeholder
	 * inside the statement string.
	 * Eg
	 * $this->execute('SELECT * FROM users WHERE nickname = :nick AND password = :pass', array('nick' => 'sniper', 'pass' => 'secret'))
	 * will result in
	 * SELECT * from users WHERE nickname = 'sniper' and password = 'secret'
	 *
	 *
	 * @param string $statement
	 * @param array $bindings - parts of the state which have to be escaped at first and than are inserted into
	 *
	 * @return mixed The result of the executed statement
	 */
	public function execute($statement, $bindings = array());


	/**
	 * Quote a value to escape it's impact in query statement.
	 *
	 * @param string $val
	 *
	 * @return string
	 */
	public function quote($val);


	/**
	 * Get the statement which have been remembered as last one executed.
	 * Even in debug mode the last one statement which should be executed is returned.
	 *
	 * @return string The statement.
	 */
	public function getLastStatement();


	public function setInflector($inflector);


	public function getInflector();

}
