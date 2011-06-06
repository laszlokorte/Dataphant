<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Properties;

use Dataphant\ComparableInterface;
use Dataphant\AggregatableInterface;
use Dataphant\SortableInterface;

/**
 * A property represence a database table's column and belongs to a Model.
 *
 * Via the ComparableInterface and the AggregateableInterface it provides methods for generating Comparsion and Operator objects
 *
 * For each data type there is an own property class
 */
interface PropertyInterface extends ComparableInterface, AggregatableInterface, SortableInterface
{
	/**
	 * Get the class the Property inherits from.
	 *
	 * @return string
	 */
	static public function getBaseProperty();

	/**
	 * Get the dataSource the property belongs to
	 *
	 * @return DataSourceInterface
	 */
	public function getDataSource();

	/**
	 * the the class name of the model the property belongs to
	 *
	 * @return string
	 */
	public function getModel();


	/**
	 * get the propertie's name
	 *
	 * @return string
	 */
	public function getName();


	/**
	 * get the propetie's name in the database
	 *
	 * @return string
	 */
	public function getFieldName();


	/**
	 * Get the propertie's type.
	 * eg String for "StringProperty"
	 *
	 * @return string
	 */
	public function getType();


	/**
	 * Is the property flagged as unique?
	 *
	 * @return boolean
	 */
	public function isUnique();


	/**
	 * should the property be lazy loaded?
	 *
	 * @return boolean
	 */
	public function isLazy();


	/**
	 * Check if the property is required not to be null
	 *
	 * @return boolean
	 */
	public function isRequired();


	/**
	 * undocumented function
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function serialize($value);


	/**
	 * undocumented function
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function unserialize($value);


	/**
	 * check if this property is a tables key
	 *
	 * @return boolean
	 */
	public function isKey();


	/**
	 * check if this property is an autincremental primary key
	 *
	 * @return boolean
	 */
	public function isSerial();


	/**
	 * Get the maximal langth of the propertie's content.
	 *
	 * @return integer
	 */
	public function getLength();

	/**
	 * Get the propertie's default value if set, otherwise null
	 *
	 * @return mixed
	 */
	public function getDefaultValueFor($record);


	/**
	 * Check if the property has an default value defined.
	 *
	 * @return boolean
	 */
	public function hasDefaultValue();


	/**
	 * get the properties value for the given resource
	 *
	 * @return mixed
	 */
	public function getValueFor($record);


	/**
	 * set the property for the given resource to the given value
	 *
	 * @return void
	 */
	public function setValueFor($record, $value);


	/**
	 * checks if the property is already loaded for the given record
	 *
	 * @return boolean
	 */
	public function isLoadedFor($record);


	/**
	 * loads the property for the given record
	 *
	 * @return boolean
	 */
	public function lazyLoadFor($record);


	/**
	 * loads the property for all the given collection's records
	 *
	 * @return boolean
	 */
	public function eagerLoadFor($collection);


	/**
	 * Check if the given value is valid value for the property.
	 *
	 * @param string $value
	 * @return void
	 */
	public function isValidValue($value);


	/**
	 * Check if the Property is writable.
	 * A property needs to be writable to be accessed via:
	 * $user->nickname = "Charlie"
	 *
	 * @return void
	 */
	public function isWriteable();


	/**
	 * Check if the property is readable.
	 * A property needs to be readable to be accessed via:
	 * $user->nickname
	 *
	 * @return void
	 */
	public function isReadable();


	/**
	 * Check if the property can be set bia "mass assignment"
	 * Mass assignment:
	 * Not setting a single property per time but an array of properties.
	 * eg:
	 * $user->setAttributes($_POST);
	 * instead of
	 * $user->nickname = $_POST['nickname'];
	 * $user->password = $_POST['password'];
	 * $user->email = $_POST['email'];
	 *
	 * @return boolean
	 */
	public function isMassAssignable();

}
