<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\States;

/**
 * The StateInterface
 */
interface StateInterface
{

	/**
	 * Commit the records state and return the clean state (clean)
	 *
	 * @return StateInterface (fluentInterface)
	 */
	public function commit();


	/**
	 * rollback the records state and return the clean state
	 *
	 * @return StateInterface (fluentInterface)
	 */
	public function rollback();


	/**
	 * returns the deleted state
	 *
	 * @return StateInterface (fluentInterface)
	 */
	public function delete();

	/**
	 * get the given properties value of the resource
	 *
	 * @param string $property
	 * @return mixed the property you asked for
	 */
	public function get($property);


	/**
	 * sets the given property to the given value and returns the new state depending on if the property changed
	 *
	 * @param PeropertyInterface $property
	 * @param string $value
	 * @return StateInterface (fluentInterface)
	 */
	public function set($property, $value);


	/**
	 * Get a associative array of changed attributes with their default values
	 *
	 * @return array list of of original attribute values
	 */
	public function getOriginalAttributes();

}
