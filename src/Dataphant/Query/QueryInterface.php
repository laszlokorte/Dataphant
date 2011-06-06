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

use ArrayAccess;

/**
 * A query contains and manages all information needed to make a database query
 * eg conditions, limit, offset, field to select...
 *
 * A query only selects data from one table
 * Joins are just used for conditions
 */
interface QueryInterface extends ArrayAccess
{
	/**
	 * Get the DataSource the query belongs to
	 *
	 * @return DataSourceInterface the DataSource the query belongs to
	 */
	public function getDataSource();


	/**
	 * Get the Model the Query belongs to
	 *
	 * @return ModelInterface
	 */
	public function getModel();


	/**
	 * Set the fields to be selected by the query
	 *
	 * @param array $fields
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setFields($fields);


	/**
	 * Get all the querie's fields.
	 * By default this are all the querie's model's fields.
	 *
	 * @return array if PropertyInterface|OperatorInterface
	 */
	public function getFields();


	public function addFields($fields);


	/**
	 * Set the relationships which should be joined eager loading by the query
	 *
	 * @param array $links
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setLinks($links);


	/**
	 * Get all the relationships the query includes (= should join on)
	 *
	 * @return array
	 */
	public function getLinks();


	public function addLinks($links);


	/**
	 * set the conditions the querie's results should match
	 *
	 * @param string $conditions
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setConditions($conditions);

	/**
	 * Get the querie's conditions
	 *
	 * @return ComparisonInterface|OperationInterface
	 */
	public function getConditions();


	public function addConditions($conditions);


	/**
	 * clear the querie's conditions to match all records
	 *
	 * @return void
	 */
	public function clearConditions();


	/**
	 * Set the querie's offset.
	 *
	 * @param integer $offset
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setOffset($offset);

	/**
	 * Get the querie's offset
	 *
	 * @return integer the queries offset
	 */
	public function getOffset();



	/**
	 * Limit the amount of the querie's result
	 *
	 * @param integer $limit
	 *
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setLimit($limit);


	/**
	 * Get the querie's limit
	 *
	 * @return integer
	 */
	public function getLimit();


	/**
	 * Set the order the querie's result should be sorted by
	 *
	 * @param array $order an array of OrderInterface objects
	 *
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setOrder($orders);

	/**
	 * Get a list of Order Objects the querie should be sorted by
	 *
	 * @return array list of Order objects
	 */
	public function getOrder();



	public function addOrder($orders);

	/**
	 * Set if the querie's results should be refetched from the DataSource even if they are already in the identity map
	 *
	 * @param boolean $reload
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setReload($reload);

	/**
	 * Should the query result replace the records in the identity map?
	 *
	 * @return boolean
	 */
	public function toBeReloaded();


	/**
	 * Set if only unique records should be fetched
	 *
	 * @param string $unique
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function setUniqueness($unique);


	/**
	 * should only uniq records be selected?
	 *
	 * @return boolean
	 */
	public function toBeUnique();


	/**
	 * Is the record valid? All needed information given?
	 *
	 * @return boolean
	 */
	public function isValid();


	/**
	 * Updates the query with another query or conditions
	 *
	 * @param QueryInterface $query
	 * @return QueryInterface the query itself (fluent interface)
	 */
	public function update($query);


	/**
	 * Merges the query with another query
	 *
	 * @param string $query
	 *
	 * @return void
	 */
	public function merge($query);


	/**
	 * apply the querie's conditions, order, limit and offset on an already fetched list of records
	 *
	 * @param array $records
	 * @return array the filtered records
	 */
	public function filterRecords($records);


	/**
	 * Check if the result of this query could be achieved just by
	 * rejecting records of the result of the given query.
	 * In other words: Does this querie's result would only
	 * contain records the other querie's result contain too?
	 *
	 * @param string $otherQuery
	 *
	 * @return boolean
	 */
	public function isSubsetOf($otherQuery);


	/**
	 * Check if the limit or the offset has been set.
	 *
	 * @return boolean
	 */
	public function isSliced();
}
