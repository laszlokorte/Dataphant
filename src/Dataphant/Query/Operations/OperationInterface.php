<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Operations;

use IteratorAggregate;
use Countable;
use Dataphant\Query\ConditionInterface;

/**
 * This interface provides methods for nesting logical operations:
 * AND, OR, NOT
 */
interface OperationInterface extends IteratorAggregate, Countable, ConditionInterface
{
	/*
		inherited from Iterator
		loop throught children/operands
	*/

	/**
	 * Get the Operation's slug
	 *
	 * @return string
	 */
	public function getSlug();


	/**
	 * Get all the children operations|Comparisons
	 *
	 * @return array
	 */
	public function getOperands();

	/**
	 * Get the parent operation
	 *
	 * @return Operation
	 */
	public function getParent();

	/**
	 * undocumented function
	 *
	 * @param string $operation
	 * @return void
	 */
	public function setParent($operation);

	/**
	 * Adds further condititons to the operation
	 *
	 * When you have a AND b AND c
	 * and you merge d and e you will get
	 * a AND b AND c AND d AND e
	 * If the operands you want to merge are an operation itself,
	 * their first level of operation will be ignored
	 *
	 * @param Interator $operands can be either an array or another operation
	 *
	 * @return OperationInterface (fluent interface)
	 */
	public function merge($operands);

}
