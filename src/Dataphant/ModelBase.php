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

use Exception;
use Closure;

use Dataphant\DataSource;

use Dataphant\Properties\PropertyInterface;
use Dataphant\Properties\PropertyBase;

use Dataphant\Relationships\OneToManyRelationship;
use Dataphant\Relationships\OneToOneRelationship;
use Dataphant\Relationships\ManyToOneRelationship;
use Dataphant\Relationships\ManyToManyRelationship;
use Dataphant\Relationships\RelationshipCollectionInterface;

use Dataphant\Query\Path;
use Dataphant\Query\ConditionInterface;

use Dataphant\States\TransientState;
use Dataphant\States\CleanState;
use Dataphant\States\DirtyState;
use Dataphant\States\DeletedState;
use Dataphant\States\ImmutableState;

use Dataphant\Query\Operations\AndOperation;
use Dataphant\Query\Comparisons\EqualToComparison;


use BadMethodCallException;
use InvalidArgumentException;
use Dataphant\Exceptions\DuplicatePropertyException;
use Dataphant\Exceptions\UnknownPropertyException;
use Dataphant\Exceptions\StiRestrictionException;
use Dataphant\Exceptions\UndefinedPropertyException;
use Dataphant\Exceptions\PropertyAccessException;
use Dataphant\Exceptions\SerialAlreadyDefinedException;

abstract class ModelBase implements ModelInterface, RecordInterface
{

	static protected $dataSourceName = DataSource::DEFAULT_NAME;

	/**
	 * The table the model is connected with
	 * By default the model's class name without the namespace
	 *
	 * @var array
	 */
	static protected $entityName = array();


	/**
	 * The property object to be the models primary key.
	 *
	 * @var array
	 */
	static protected $serial = array();


	static protected $keys = array();



	/**
	 * Discriminators
	 *
	 * @var array
	 */
	static protected $discriminator = array();


	/**
	 * 2d array of all model's all properties keyed model's class name and by propertie's name
	 *
	 * array('User' => array('nickname' => PropertyInterfface), ...)
	 *
	 * @var array
	 */
	static protected $properties = array();


	/**
	 * 2d array of all models all relationships, keyed by model's class name and by relationship name
	 *
	 * array('User' => array('squads' => RelationshipInterfface), ...)
	 *
	 * @var array
	 */
	static protected $relationships = array();


	/**
	 * The registered callback function
	 *
	 * array( 'beforeSave' => array(function($p){}, function($p){}), 'afterUpdate' => array(...), ...)
	 *
	 * @var array
	 */
	static protected $callBacks = array();


	/**
	 * All the models predefined scopes
	 *
	 * @var array
	 */
	static protected $definedScopes = array();


	/**
	 * The datasources registeres with the model. Allows one model to work with multiple databases
	 *
	 * @var array
	 */
	static protected $dataSources = array();



	/**
	 * Caches the model's base models
	 *
	 * @var array
	 */
	static protected $baseModel = array();


	protected $recursions = array();


	/**
	 * The records' properties' values keyed by the property name
	 *
	 * @var string
	 */
	protected $attributes = array();

	/**
	 * The records state
	 *
	 * @var StateInterface
	 */
	protected $state;


	/**
	 * caches the class name
	 *
	 * @var string
	 */
	protected $model;


	/**
	 * The records key in the identity map.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The collection the record belongs to currently.
	 *
	 * @var CollectionInterface
	 */
	protected $collection;

	static public function defineProperty($name, $options = array())
	{
		$calledClass = get_called_class();

		if(isset(static::$properties[$calledClass][$name]))
		{
			throw new DuplicatePropertyException("Property with name '{$name}' has already been defined.");
		}

		if(isset($options['type']))
		{
			$type = $options['type'];
		}
		else
		{
			$type = 'String';
		}

		$property = PropertyBase::createPropertyOfType($type, $calledClass, $name, $options);

		if($property->isSerial())
		{
			static::setSerial($property);
		}
		if($property->isKey())
		{
			static::addKey($property);
		}

		static::$properties[$calledClass][$name] = $property;

		return static::$properties[$calledClass][$name];
	}

	static public function getProperty($name)
	{
		$properties = static::getProperties();

		if(isset($properties[$name]))
		{
			return $properties[$name];
		}
		else
		{
			$class = get_called_class();
			throw new UnknownPropertyException("Property '{$name}' des not exist for model '{$class}");
		}
	}

	static public function getProperties()
	{
		$baseModelProperties = static::getBaseModelProperties();

		if(empty($baseModelProperties))
		{
			static::getKeys();
		}

		$calledClass = get_called_class();

		if( ! isset(static::$properties[$calledClass]))
		{
			static::$properties[$calledClass] = array();
		}
		return array_merge($baseModelProperties, static::$properties[$calledClass]);
	}


	static protected function getBaseModelProperties()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$baseModelProperties = $baseModel::getProperties();
		}
		else
		{
			$baseModelProperties = array();
		}

		return $baseModelProperties;
	}

	static public function getDefaultProperties()
	{
		$properties = static::getProperties();

		$defaultProperties = array_filter($properties, function($property) {
			return !$property->isLazy();
		});

		return array_merge(static::getKeys(), $defaultProperties);
	}

	static public function hasOne($name, $options = array())
	{
		$calledClass = get_called_class();
		$relationship = new OneToOneRelationship($name, $calledClass, $options['class'], $options);
		static::$relationships[$calledClass][$name] = $relationship;

		return $relationship;
	}

	static public function hasMany($name, $options = array())
	{
		$calledClass = get_called_class();
		$relationship = new OneToManyRelationship($name, $calledClass, $options['class'], $options);
		static::$relationships[$calledClass][$name] = $relationship;

		return $relationship;
	}

	static public function hasAndBelongsToMany($name, $options = array())
	{
		$calledClass = get_called_class();
		$relationship = new ManyToManyRelationship($name, $calledClass, $options['class'], $options);
		static::$relationships[$calledClass][$name] = $relationship;

		return $relationship;
	}

	static public function belongsTo($name, $options = array())
	{
		$calledClass = get_called_class();
		$relationship = new ManyToOneRelationship($name, $calledClass, $options['class'], $options);
		static::$relationships[$calledClass][$name] = $relationship;

		return $relationship;
	}

	static public function getRelationships()
	{
		$baseModelRelationships = static::getBaseModelRelationships();

		$calledClass = get_called_class();
		if( ! isset(static::$relationships[$calledClass]))
		{
			static::$relationships[$calledClass] = array();
		}
		return array_merge($baseModelRelationships, static::$relationships[$calledClass]);
	}

	static protected function getBaseModelRelationships()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$baseModelRelationships = $baseModel::getRelationships();
		}
		else
		{
			$baseModelRelationships = array();
		}

		return $baseModelRelationships;
	}

	protected function getParentRelationships()
	{
		$parentRelationships = array();
		$relationships = static::getRelationships();

		foreach($relationships AS $relationship)
		{
			if( ! $relationship instanceof ManyToOneRelationship)
			{
				continue;
			}
			if( ! $relationship->isLoadedFor($this))
			{
				continue;
			}
			if($relationship->getValueFor($this) === NULL)
			{
				continue;
			}
			$parentRelationships[] = $relationship;
		}

		return $parentRelationships;
	}


	protected function getChildRelationships()
	{
		$childRelationships = array();
		$relationships = static::getRelationships();

		foreach($relationships AS $relationship)
		{
			if( ! $relationship instanceof OneToManyRelationship)
			{
				continue;
			}
			if( ! $relationship->isLoadedFor($this))
			{
				continue;
			}

			$childRelationships[] = $relationship;
		}

		return $childRelationships;
	}


	static public function setEntityName($entityName)
	{
		if(static::getBaseModel() !== NULL)
		{
			throw new StiRestrictionException('You can not change the entity name of an STI child class');
		}
		static::$entityName[get_called_class()] = $entityName;
	}

	static public function getEntityName()
	{
		if(static::getBaseModel())
		{
			return static::getBaseModelEntityName();
		}

		$calledClass = get_called_class();

		if(empty(static::$entityName[$calledClass]))
		{
			$entityNameArray = explode('\\', $calledClass);
			$entityName = end($entityNameArray);

			static::$entityName[$calledClass] = $entityName;
		}

		return static::$entityName[$calledClass];
	}

	static protected function getBaseModelEntityName()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$entityName = $baseModel::getEntityName();
		}
		else
		{
			$entityName = '';
		}

		return $entityName;
	}

	static public function getSerial()
	{
		$calledClass = get_called_class();
		if( ! isset(static::$serial[$calledClass]))
		{
			if(static::getBaseModel())
			{
				return static::getBaseModelSerial();
			}
			else
			{
				static::getKeys();
				if( ! isset(static::$serial[$calledClass]))
				{
					static::$serial[$calledClass] = NULL;
				}
			}
		}

		return static::$serial[$calledClass];
	}

	static protected function getBaseModelSerial()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$serial = $baseModel::getSerial();
		}
		else
		{
			$serial = NULL;
		}

		return $serial;
	}


	static protected function setSerial($property)
	{
		if(static::getBaseModel() !== NULL)
		{
			throw new StiRestrictionException('You can not change the serial property of an STI child class');
		}

		$calledClass = get_called_class();

		if( isset(static::$serial[$calledClass]))
		{
			# TODO: use a more specific exception
			throw new SerialAlreadyDefinedException('The serial has already been set and is in use. You can not change it anymore.');
		}
		static::$serial[$calledClass] = $property;
	}


	static public function getDiscriminator()
	{
		$calledClass = get_called_class();

		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$discriminator = $baseModel::getDiscriminator();
			if($discriminator === NULL)
			{
				$baseModel::setDiscriminator('class');
				return $baseModel::getDiscriminator();
			}
			else
			{
				return $discriminator;
			}
		}
		elseif( ! isset(static::$discriminator[$calledClass]))
		{
			static::$discriminator[$calledClass] = NULL;
		}

		return static::$discriminator[$calledClass];
	}

	static public function setDiscriminator($name)
	{
		if(static::getBaseModel() !== NULL)
		{
			throw new StiRestrictionException('The discriminator has to be set in the parent Model.');
		}

		static::$discriminator[get_called_class()] = static::defineProperty($name, array('type' => 'Discriminator'));
	}


	static protected function addKey($property)
	{
		if(static::getBaseModel())
		{
			throw new StiRestrictionException('Keys can only be defined in the STI base model.');
		}
		else
		{
			$calledClass = get_called_class();
			static::$keys[$calledClass]['keys'][$property->getName()] = $property;
		}
	}


	static public function getKeys()
	{
		if(static::getBaseModel())
		{
			return static::getBaseModelKeys();
		}
		else
		{
			$calledClass = get_called_class();
			if( ! isset(static::$keys[$calledClass]['keys']))
			{
				static::$keys[$calledClass]['keys'] = array();
				static::defineProperty('id', array('type' => 'Serial'));
			}

			return static::$keys[$calledClass]['keys'];
		}
	}

	static protected function getBaseModelKeys()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			$keys = $baseModel::getKeys();
		}
		else
		{
			$keys = NULL;
		}

		return $keys;
	}

	public function getKey()
	{
		if( ! isset($this->key) || $this->key === NULL)
		{
			$model = $this->getModel();
			$keyValues = array();
			$keys = $model::getKeys();
			$originalAttributes = $this->getState()->getOriginalAttributes();
			foreach($keys AS $key)
			{
				if(isset($originalAttributes[$key->getFieldName()]))
				{
					$value = $originalAttributes[$key->getFieldName()];
				}
				else
				{
					if($key->isLoadedFor($this)===FALSE)
					{
						return NULL;
					}
					$value = $key->getValueFor($this);
				}
				$keyValues[] = $key->getFieldName() . ':' . $value;
			}
			$this->key = join(';', $keyValues);
		}

		return $this->key;
	}

	static public function beforeSave($callBackFunction)
	{

	}

	static public function beforeCreate($callBackFunction)
	{

	}

	static public function beforeUpdate($callBackFunction)
	{

	}

	static public function beforeDelete($callBackFunction)
	{

	}

	static public function afterSave($callBackFunction)
	{

	}

	static public function afterCreate($callBackFunction)
	{

	}

	static public function afterUpdate($callBackFunction)
	{

	}

	static public function afterDelete($callBackFunction)
	{

	}

	static public function getDataSource()
	{
		if(($baseModel = static::getBaseModel()) !== NULL)
		{
			return $baseModel::getDataSource();
		}
		return DataSource::getByName(static::$dataSourceName);
	}


	static public function find()
	{
		$baseCollection = new Collection(get_called_class());
		return $baseCollection->all(static::getDefaultScope());
	}

	static protected function getDefaultScope()
	{
		$thisClass = get_called_class();
		$scope = array();
		if(static::getBaseModel() !== NULL)
		{
			$discriminator = static::getDiscriminator();
			$scope['conditions'] = $discriminator->eq($thisClass);
		}
		return $scope;
	}


	static public function build($data = array())
	{
		$record = new static();
		$record->setState(new TransientState($record));

		$record->setAttributes($data);

		return $record;
	}

	static public function defineScope($name, $definition)
	{
		if($definition instanceof CollectionInterface)
		{
			$definition = clone $definition;
		}
		elseif($definition instanceof Closure)
		{

		}
		else
		{
			throw new InvalidArgumentException('Scope have to be a collection or a closure');
		}

		static::$definedScopes[get_called_class()][$name] = $definition;
	}

	static public function getScope($name, $params = array())
	{
		$scope = static::$definedScopes[get_called_class()][$name];
		if($scope instanceof Closure)
		{
			return call_user_func_array($scope, $params);
		}
		else
		{
			return $scope;
		}
	}

	static public function isScopeDefined($name)
	{
		return isset(static::$definedScopes[get_called_class()][$name]);
	}

	static public function map($data, $query)
	{
		$dataSource = $query->getDataSource();
		$dataSourceName = $dataSource->getName();
		$fields = $query->getFields();
		$reload = $query->toBeReloaded();
		$model = get_called_class();
		$discriminator = $model::getDiscriminator();

		$records = array();


		// loop through all rows to be mapped to one object each
		foreach($data AS $row)
		{
			if($discriminator !== NULL && ($discrVal = $discriminator->unserialize($row[$discriminator->getName()])))
			{
				$baseModel = $model::getBaseModel();
				if($baseModel === NULL) $baseModel = $model;

				if($discriminator->isValidValue($discrVal))
				{
					$model = $discrVal;
				}
				else
				{
					throw new DifferentialModelException("{$discrVal} is no subclass of {$baseModel}.");
				}
			}

			// load the identity map for this model
			$identityMap = $dataSource->getIdentityMap($model);
			//get all the models properties which are keys
			$keys = $model::getKeys();
			$keyMap = array();

			foreach($keys AS $key)
			{
				if(isset($row[$key->getName()]) && $key->isValidValue($row[$key->getName()]))
				{
					$keyMap [] = $key->getFieldName() . ':' . $row[$key->getName()];
				}
				else
				{
					// If not all primary keys has been selected
					$keyMap = array();
					$identityMap = NULL;
					break;
				}
			}

			$key = join(';', $keyMap);

			if($identityMap && isset($identityMap[$key]))
			{
				$record = $identityMap[$key];
			}
			else
			{
				$record = new $model();
			}

			foreach($fields AS $property)
			{

				if($reload || !$property->isLoadedFor($record))
				{
					$value = $property->unserialize($row[$property->getName()]);
					$property->setValueFor($record, $value);
				}
			}

			if($identityMap !== NULL)
			{
				$identityMap[$key] = $record;
				if( ! $record->getState() instanceof TransientState)
				{
					$record->setState(new CleanState($record));
				}
			}
			else
			{
				$record->setState(new ImmutableState($record));
			}


			$records[] = $record;
		}

		return $records;
	}


	static public function getBaseModel()
	{
		$calledClass = get_called_class();
		if( ! isset(static::$baseModel[$calledClass]))
		{
			$baseModel = get_parent_class($calledClass);

			if($baseModel !== __CLASS__)
			{
				if($secondBase = $baseModel::getBaseModel())
				{
					static::$baseModel[$calledClass] = $secondBase;
				}
				else
				{
					static::$baseModel[$calledClass] = $baseModel;
				}
			}
			else
			{
				static::$baseModel[$calledClass] = NULL;
			}
		}

		return static::$baseModel[$calledClass];
	}


	static public function getNewQuery($otherQuery = NULL)
	{
		$calledClass = get_called_class();
		return static::getDataSource()->getNewQuery($calledClass, $otherQuery);
	}


	/**
	 * Constructor should not be called directly
	 */
	protected function __construct()
	{

	}

	public function getAttribute($propertyName)
	{
		$model = $this->getModel();
		$properties = $model::getProperties();
		$relationships = $model::getRelationships();

		if(isset($properties[$propertyName]))
		{
			if( ! $properties[$propertyName]->isReadable())
			{
				throw new PropertyAccessException("The Property is protected and can not be read.");
			}
			return $this->getState()->get($properties[$propertyName]);
		}
		elseif(isset($relationships[$propertyName]))
		{
			return $this->getState()->get($relationships[$propertyName]);
		}
		else
		{
			# TODO: more specific exception
			throw new UndefinedPropertyException("There is no property {$propertyName} for {$model}");
		}
	}


	public function setAttribute($propertyName, $propertyValue)
	{
		$model = $this->getModel();
		$properties = $model::getProperties();
		$relationships = $model::getRelationships();

		if(isset($properties[$propertyName]))
		{
			if( ! $properties[$propertyName]->isWriteable())
			{
				throw new PropertyAccessException("The Property is protected and can not be set.");
			}
			$this->setState($this->state->set($properties[$propertyName], $propertyValue));
		}
		elseif(isset($relationships[$propertyName]))
		{
			$this->setState($this->state->set($relationships[$propertyName], $propertyValue));
		}
		else
		{
			# TODO: more specific exception
			throw new UndefinedPropertyException("There is no property {$propertyName} for {$model}");
		}
	}


	public function setAttributes($attributes)
	{
		foreach($attributes AS $attribute => $value)
		{
			if($this->isMassAssignable($attribute) === TRUE)
			{
				$this->setAttribute($attribute, $value);
			}
		}
	}


	protected function isMassAssignable($attribute)
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		if(isset($properties[$attribute]) && $properties[$attribute]->isMassAssignable() === TRUE)
		{
			return TRUE;
		}

		$relationships = $model::getRelationships();

		if(isset($relationships[$attribute]) && $relationships[$attribute]->isMassAssignable() === TRUE)
		{
			return TRUE;
		}

		return FALSE;
	}


	public function getAttributes()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		$attributes = array();
		foreach($properties AS $property)
		{
			if($property->isLoadedFor($this) && $property->isReadable($this))
			{
				$attributes[$property->getName()] = $property->getValueFor($this);
			}
		}

		return $attributes;
	}


	public function isAttributeLoaded($propertyName)
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		if( ! isset($properties[$propertyName]))
		{
			throw new UndefinedPropertyException("There is no property {$propertyName} for {$model}");
		}

		return $properties[$propertyName]->isLoadedFor($this);
	}


	public function hasAttribute($attributeName)
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		if(isset($properties[$attributeName]))
		{
			return TRUE;
		}

		$relationships = $model::getRelationships();

		if(isset($relationships[$attributeName]))
		{
			return TRUE;
		}


		return FALSE;
	}


	public function getDirtyAttributes()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();
		$attributes = $this->getState()->getOriginalAttributes();
		$dirtyAttributes = array();

		foreach($attributes AS $key => $attr)
		{
			if(isset($properties[$key]))
			{
				$dirtyAttributes[$key] = $this->getAttribute($key);
			}
		}

		return $dirtyAttributes;
	}

	public function reload()
	{
		$this->setState($this->getState()->rollback());
	}


	public function save($runHooks = TRUE)
	{
		if($this->preventRecursive(__METHOD__))
		{
			$result = ($this->saveParents() && $this->saveSelf() && $this->saveChildren());
			$this->resolveRecursion(__METHOD__);

			return $result;
		}
		else
		{
			return TRUE;
		}
	}

	protected function preventRecursive($name)
	{
		if(isset($this->recursions[$name]))
		{
			return FALSE;
		}
		else
		{
			$this->recursions[$name] = TRUE;
			return TRUE;
		}
	}

	protected function resolveRecursion($name)
	{
		unset($this->recursions[$name]);
	}

	public function saveParents()
	{
		if($this->preventRecursive(__METHOD__))
		{
			$success = TRUE;
			$relationships = $this->getParentRelationships();
			foreach($relationships AS $relationship)
			{
				$parent = $relationship->getValueFor($this);
				$success = $success && $parent->saveParents();
			}

			$this->resolveRecursion(__METHOD__);
			return $success;
		}
		else
		{
			return TRUE;
		}
	}

	protected function saveSelf()
	{
		$this->persist();

		return $this->isClean();
	}

	protected function saveChildren()
	{
		$success = TRUE;
		$relationships = $this->getChildRelationships();
		foreach($relationships AS $relationship)
		{
			$childCollection = $relationship->getValueFor($this);
			$success = $success && $childCollection->save();
		}

		return $success;
	}

	public function destroy($runHooks = TRUE)
	{
		if($this->isDestroyed())
		{
			return TRUE;
		}

		$this->setState($this->getState()->delete());

		$this->persist();

		return $this->isDestroyed();
	}


	public function getState()
	{
		return $this->state;
	}


	public function setState($state)
	{
		$this->state = $state;
	}


	public function isNew()
	{
		return $this->getState() instanceof TransientState;
	}


	public function isDestroyed()
	{
		return $this->isReadonly() && $this->getKey() !== NULL;
	}


	public function isClean()
	{
		return $this->getState() instanceof CleanState || $this->getState() instanceof ImmutableState;
	}


	public function isDirty()
	{
		return $this->getState() instanceof DirtyState;
	}


	public function isReadonly()
	{
		return $this->getState() instanceof ImmutableState;
	}


	public function getModel()
	{
		if( ! isset($this->model))
		{
			$this->model = get_class($this);
		}

		return $this->model;
	}


	public function setCollection($collection)
	{
		$this->collection = $collection;
	}

	public function getCollection($forSelf = TRUE)
	{
		if(! isset($this->collection))
		{
			if($forSelf === TRUE)
			{
				return $this->getCollectionForSelf();
			}
			else
			{
				return NULL;
			}
		}

		if($this->collection instanceof RelationshipCollectionInterface)
		{
			if($this->collection->getSource()->getCollection()->hasChildCollectionFor($this->collection->getRelationship()))
			{
				return $this->collection->getSource()->getCollection()->getChildCollectionFor($this->collection->getRelationship());
			}
		}

		return $this->collection;
	}

	public function getCollectionForSelf()
	{
		return new Collection(get_class($this), $this->getQueryForSelf(), array($this));
	}

	public function getQueryForSelf()
	{
		$model = $this->getModel();
		$dataSource = $model::getDataSource();
		return $dataSource->getNewQuery($model, array('fields' => $this->getFields(), 'conditions' => $this->getConditions()));
	}

	protected function getFields()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();
		$fields = array();
		foreach($properties AS $property)
		{
			if($property->isLoadedFor($this))
			{
				$fields[] = $property;
			}
		}

		return $fields;
	}

	protected function getConditions()
	{
		$key = $this->getKey();
		if($key)
		{
			$model = $this->getModel();
			$keys = $model::getKeys();
			$conditions = array();
			$originalAttributes = $this->getState()->getOriginalAttributes();

			foreach($keys AS $key)
			{
				/*
					Make sure to use the persisted key in case it changed
					otherwise the record an not be recoverd in the database when it should be updated
				*/
				if(isset($originalAttributes[$key->getFieldName()]))
				{
					$value = $originalAttributes[$key->getFieldName()];
				}
				else
				{
					$value = $key->getValueFor($this);
				}
				$conditions[] = $key->eq($value);
			}
			return new AndOperation($conditions);
		}
		else
		{
			$conditions = array();
			$model = $this->getModel();
			$properties = $model::getProperties();

			foreach($properties AS $property)
			{
				if($property->isLoadedFor($this))
				{
					$conditions[] = new EqualToComparison($property, $property->getValueFor($this));
				}
			}

			return new AndOperation($conditions);
		}


	}


	/**
	 * catches all property reading accesses and delegates them either to the matching property or to the matching associated record or collection
	 *
	 * @param string $key the property or association to get
	 * @return mixed|Collection|Record
	 */
	public function __get($key)
	{
		return $this->getAttribute($key);
	}


	/**
	 * catches all property writing accesses and delegates them either to the matching property or to the matching accosiated record or collection
	 *
	 * @param string $key the property or association to set
	 * @param mixed $value the value to set the property or association to
	 * @return mixed|Collection|Record
	 */
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}


	public function __isset($key)
	{
		return $this->hasAttribute($key);
	}

	/**
	 * Delegates method calls on the class to one of
	 * (a Path to a matching relation| a property)
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return void
	 */
	static public function __callStatic($method, $arguments)
	{
		$properties = static::getProperties();
		if(isset($properties[$method]))
		{
			return $properties[$method];
		}

		$relationships = static::getRelationships();
		if(isset($relationships[$method]))
		{
			return new Path(array($relationships[$method]));
		}

		$class = get_called_class();
		throw new BadMethodCallException("Undefined method call '{$method}' for {$class}");
	}


	/**
	 * Delegates method calls on a record to one of
	 * (a collection of a matching relation|a data value)
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return void
	 */
	public function __call($method, $arguments)
	{
		throw new Exception('ModelBase::__call($a='.$method.', $b) is not yet implemented.');
	}

	public function offsetSet($offset, $value)
	{
		return $this->setAttribute($offset, $value);
	}

	public function offsetGet($offset)
	{
		return $this->getAttribute($offset);
	}

	public function offsetExists($offset)
	{
		return $this->hasAttribute($offset);
	}

	public function offsetUnset($offset)
	{
		throw new BadMethodCallException('Can not unset Property');
	}

	public function __toString()
	{
		$class = get_class();
		return "<#{$class}>";
	}

	protected function persist()
	{
		$this->setState($this->getState()->commit());
	}
}
