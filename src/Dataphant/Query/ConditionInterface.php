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
 * A condition is either a comparison or multiple comparisons grouped by one or multiple operations
 */
interface ConditionInterface
{

	/**
	 * checks if a record matches the condition
	 *
	 * @param RecordInterface $record
	 * @return boolean
	 */
	public function match($record);


	/**
	 * Make the record match the condition
	 *
	 * @param RecordInterface $record
	 *
	 * @return void
	 */
	public function applyToRecord($record);


	/**
	 * Check if the Condition contains all information to be used in a database query
	 *
	 * @return boolean
	 */
	public function isValid();


	/**
	 * create a new OR operation linking the current condition with the given one
	 *
	 * @param OperationInterface $otherOperation the other condition
	 * @return OrOperation
	 */
	public function or_($otherOperation);

	/**
	 * create a new AND operation linking the current condition with the given one
	 *
	 * @param OperationInterface $otherOperation the other condition
	 * @return AndOperation
	 */
	public function and_($otherOperation);

	/**
	 * create a new AND NOT operation linking the current condition with the given one
	 *
	 * @param OperationInterface $otherOperation the other condition
	 * @return AndOperation
	 */
	public function andNot_($otherOperation);

}
