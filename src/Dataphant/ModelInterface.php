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
 * A model(class) represents one type of data to be stored in the database(table)
 * It cosists of a count of properties (database columns)
 * and can have relationships to other models (foreign keys)
 *
 * These conditions can be defined by using the static methods
 *
 * By default each instance of a model represents a database row
 */
interface ModelInterface
{

	/**
	 * add a property to a model
	 *
	 * @param string $name
	 * @param string $type the properties data type
	 * @param string $options
	 * @return PropertyInterface The added property
	 */
	static public function defineProperty($name, $options = array());


	/**
	 * Get the property with the given name.
	 *
	 * @param string $name
	 *
	 * @return PropertyInterface
	 */
	static public function getProperty($name);


	/**
	 * Get an associative list of all the model's properties keyed by their names
	 *
	 * @return array
	 */
	static public function getProperties();

	/**
	 * associate a model with another one (1to1 cardinality)
	 *
	 * @param string $name the associations name
	 * @param string $options additional options for customization
	 * @return void
	 */
	static public function hasOne($name, $options = array());

	/**
	 * associate a model with another one (1toN cardinality)
	 *
	 * @param string $name
	 * @param string $options
	 * @return void
	 */
	static public function hasMany($name, $options = array());

	/**
	 * associate a model with another one (NtoM cardinality)
	 *
	 * @param string $name
	 * @param string $options
	 * @return void
	 */
	static public function hasAndBelongsToMany($name, $options = array());

	/**
	 * associate a model with another one (Nto1 cardinality)
	 *
	 * @param string $name the associations name
	 * @param string $options additional options for customization
	 * @return void
	 */
	static public function belongsTo($name, $options = array());

	/**
	 * Get all the relationships the model has
	 *
	 * @return array() an associative array of the model's relationship objects keyed by the relationships' name
	 */
	static public function getRelationships();


	/**
	 * Set the name of the entity the model represents.
	 *
	 * @return void
	 */
	static public function setEntityName($entityName);


	/**
	 * Get the name of the Entity the model represents
	 * By default it is the model's class name without it's namespace.
	 *
	 * @return string
	 */
	static public function getEntityName();

	/**
	 * Get the dataSource the model gets it's data from
	 *
	 * @return DataSourceInterface
	 */
	static public function getDataSource();

	/**
	 * Get the model's primary key. When no serial has been set before, a lazy initialized property named "id" get returned.
	 *
	 * @return PropertyInterface
	 */
	static public function getSerial();


	/**
	 * Get all properties which are keys
	 *
	 * @return void
	 */
	static public function getKeys();

	/**
	 * get a lazy loading collection of the model's records
	 *
	 * @return CollectionInterface
	 */
	static public function find();


	/**
	 * builds a new instance of the model
	 *
	 * @param string $data Initial attribute values
	 * @return ModelBase
	 */
	static public function build($data = array());

	/**
	 * Predefine a collection definition (=scope) for applying it later on other collections.
	 *
	 * @param string $name The scope's name
	 * @param string $collection Either a collection or a function returning a collection
	 * @return void
	 */
	static public function defineScope($name, $collection);

	/**
	 * Get a predefined collection definition (=scope) by it's name.
	 *
	 * @param string $name The scope's name
	 * @param array $params The params to call the scope function with
	 * @return CollectionInterface
	 */
	static public function getScope($name, $params = array());


	/**
	 * Check if a scope with the given $name is defined for the Model.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	static public function isScopeDefined($name);


	/**
	 * maps an database result into model objects and put them into the identity map
	 *
	 * @param string $data
	 * @return void
	 */
	static public function map($data, $query);

	/**
	 * register a function to be called before a record get's saved
	 *
	 * the callback function should except one parameter which the record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function beforeSave($callBackFunction);


	/**
	 * register a function to be called before a record get's created (saved the first time)
	 *
	 * the callback function should except one parameter which the record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function beforeCreate($callBackFunction);


	/**
	 * register a function to be called before a record get's updated (saved, except the first time)
	 *
	 * the callback function should except one parameter which the record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function beforeUpdate($callBackFunction);


	/**
	 * register a function to be called before a record get's deleted
	 *
	 * the callback function should except one parameter which the record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function beforeDelete($callBackFunction);


	/**
	 * register a function to be called after a record got saved
	 *
	 * the callback function should except one parameter which the savef record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function afterSave($callBackFunction);


	/**
	 * register a function to be called after a record got created (saved the first time)
	 *
	 * the callback function should except one parameter which the created record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function afterCreate($callBackFunction);

	/**
	 * register a function to be called after a record got updated (saved, except the first time)
	 *
	 * the callback function should except one parameter which the updated record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function afterUpdate($callBackFunction);


	/**
	 * register a function to be called after a record got deleted
	 *
	 * the callback function should except one parameter which the deleted record will be passed to
	 *
	 * @param closure $callBackFunction
	 * @return void
	 */
	static public function afterDelete($callBackFunction);


	/**
	 * Get the parent class of the Model
	 *
	 * @return string
	 */
	static public function getBaseModel();


	static public function setDiscriminator($name);


	static public function getDiscriminator();


	static public function getNewQuery($otherQuery = NULL);

}
