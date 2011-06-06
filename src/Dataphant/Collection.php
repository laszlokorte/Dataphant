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

use Dataphant\CollectionIterator;

use BadMethodCallException;

use Dataphant\Utils\ArrayTools;

use Dataphant\Exceptions\UnexpectedResultException;
use Dataphant\Exceptions\DifferentialModelException;

class Collection implements CollectionInterface
{

	/**
	 * List of already fetch records
	 *
	 * @var array
	 */
	protected $records = array();


	/**
	 * List of records removed from the collection
	 *
	 * @var string
	 */
	protected $removedRecords = array();


	/**
	 * The query the collection is based of
	 *
	 * @var QueryInterface
	 */
	protected $query;


	/**
	 * The iteration index of the current record.
	 *
	 * @var integer
	 */
	protected $currentRecord;

	/**
	 * Stores the collection the current record in the iteration was associated with before.
	 *
	 * @var CollectionInterface
	 */
	protected $currentRecordOriginalCollection;


	/**
	 * If the collection is already loaded.
	 *
	 * @var boolean
	 */
	protected $loaded = FALSE;


	/**
	 * undocumented variable
	 *
	 * @var array
	 */
	protected $childCollections = array();


	/**
	 * undocumented variable
	 *
	 * @var string
	 */
	protected $aggregations = array();


	/**
	 * The model the collection belongs to
	 *
	 * @var string
	 */
	protected $model;


	/**
	 * build a new collection
	 *
	 * @param QueryInterface $query Query the collection will be build on
	 * @param array $records list of records to initialize the collection with
	 */
	public function __construct($model, $query = NULL, $records = NULL)
	{
		if($query && $query->getModel() !== $model && ! is_subclass_of($query->getModel(), $model))
		{
			throw new DifferentialModelException($model . '<>' . $query->getModel());
		}
		$this->model = $model;
		$this->query = $query;
		$this->records = array();

		if($records !== NULL)
		{
			$this->markLoaded();

			$this->records = $query->filterRecords($records);
		}

	}


	public function get($key)
	{
		$keyArray = is_array($key) ? $key : array($key);
		$model = $this->getModel();
		$keys = $model::getKeys();

		$keyValueList = array();
		reset($keyArray);
		foreach($keys AS $property)
		{
			$keyList[] = $property->getFieldName() . ':' . current($keyArray);
			next($keyArray);
		}
		$keyValue = join(';', $keyList);

		$idMap = $this->getDataSource()->getIdentityMap($this->getModel());
		if(isset($idMap[$keyValue]))
		{
			if($this->query === NULL || $this->query->getConditions()->match($idMap[$keyValue]))
			{
				return $idMap[$keyValue];
			}
		}
	}


	public function one($options = array())
	{
		$collection = $this->all($options);

		if(count($collection) > 1)
		{
			throw new UnexpectedResultException('More than one record has been found.');
		}
		elseif(count($collection) < 1)
		{
			throw new UnexpectedResultException('No record has been found.');
		}

		return $collection[0];
	}

	public function first($options = array())
	{
		$collection = $this->all($options);

		return isset($collection[0]) ? $collection[0] : NULL;
	}


	public function all($options = array())
	{
		$options = $this->typeCastFinderOptions($options);

		if(empty($options))
		{
			return $this;
		}

		if(is_object($options) && $this->isVirgin())
		{
			$oldQuery = NULL;
			$newQuery = $options;
		}
		else
		{
			$oldQuery = $this->query;
			$newQuery = $this->getNewQuery($options);
		}


		if( ! $this->isLoaded() || $oldQuery === NULL || ! $newQuery->isSubsetOf($oldQuery))
		{
			$records = NULL;
		}
		else
		{
			$records = $this->records;
		}
		$collection = $this->createNewCollection($newQuery, $records);

		return $collection;
	}


	protected function isVirgin()
	{
		return $this->query == NULL && ! $this->isLoaded();
	}


	/**
	 * Convert different types of parameters to uniformed collection object containing
	 * The given options.
	 *
	 * eg when you pass in an OrderObject and you will a collection sorted by the given order
	 * when you pass in an Condition you get a collection filtered by the condition
	 * you can pass in multiple options at once by passing an associative array
	 *
	 * **STILL NEEDS TO BE IMPLEMENTED**
	 *
	 * @param string $options
	 *
	 * @return void
	 */
	protected function typeCastFinderOptions($options)
	{
		return $options;
	}


	public function filter($conditions)
	{
		$collection = $this->all(array('conditions' => $conditions));

		return $collection;
	}


	protected function createNewCollection($query, $records = NULL)
	{
		$collection = new static($this->getModel(), $query, $records);

		# set the modification mode of the new collection to itensify
		return $collection;
	}


	public function eagerLoad($fields)
	{
		$fields = ArrayTools::flatten(func_get_args());

		# Eager loading only works if the collection does not have been loaded yet.
		if( ! $this->isLoaded())
		{
			$options = array('fields' => array_merge($this->getQuery()->getFields(), $fields));
		}
		else
		{
			$options = array();
		}
		return $this->all($options);
	}


	public function lazyLoad($fields)
	{
		$fields = ArrayTools::flatten(func_get_args());

		if( ! $this->isLoaded())
		{
			$query = clone $this->getQuery();
			$currentFields = $query->getFields();
			$newFields = array();

			# array_diff does not work for object array
			foreach($currentFields AS $key => $property)
			{
				if( ! in_array($key, $fields, TRUE) && ! in_array($property, $fields, TRUE) && ! array_key_exists($key, $fields))
				{
					$newFields[$key] = $property;
				}
			}

			$options = array('fields' => $newFields);
		}
		else
		{
			$options = array();
		}


		$collection = $this->all($options);

		return $collection;
	}

	public function limit($size)
	{
		return $this->all(array('limit' => $size));
	}


	public function skip($offset)
	{

		$offset += $this->getQuery()->getOffset();

		if($offset < 0)
		{
			$offset = 0;
		}

		return $this->all(array('offset' => $offset));
	}


	public function orderBy($order)
	{
		$order = ArrayTools::flatten(func_get_args());

		return $this->all(array('order' => $order));
	}


	public function uniq()
	{
		return $this->all(array('unique' => TRUE));
	}


	public function getQuery()
	{
		if( ! $this->query)
		{
			$this->query = $this->getNewQuery();
		}

		return $this->query;
	}


	public function getDataSource()
	{
		$model = $this->getModel();

		return $model::getDataSource();
	}


	public function getModel()
	{
		return $this->model;
	}


	public function forceLoad()
	{
		$this->loadRecords();
	}


	public function reload()
	{
		$this->reset();
		$this->getQuery()->setReload(TRUE);
		$this->forceLoad();

		return $this;
	}


	public function reset()
	{
		$this->markNotLoaded();
		$this->records = array();
	}


	public function isLoaded()
	{
		return $this->loaded === TRUE;
	}


	public function getArray()
	{
		$dataSource = $this->getDataSource();

		return $dataSource->read($this->getQuery(), TRUE);
	}


	protected function loadRecords()
	{
		if( ! $this->isLoaded())
		{
			$this->markLoaded();

			$dataSource = $this->getDataSource();

			$this->records = array_merge($this->records, $dataSource->read($this->getQuery()));

			$this->markLoaded();
		}
	}


	protected function markLoaded()
	{
		$this->loaded = TRUE;
	}


	protected function markNotLoaded()
	{
		$this->loaded = FALSE;
	}


	public function getIterator()
	{
		return new CollectionIterator($this);
	}


	public function count()
	{
		$this->loadRecords();

		return count($this->records);
	}


	public function offsetGet($offset)
	{
		$this->loadRecords();

		return $this->records[$offset];
	}


	public function offsetSet($offset, $value)
	{
		$this->loadRecords();

		# $collection[] = ...
		if($offset === NULL)
		{
			$this->records[] = $this->markRecordAdded($value);
		}
		# $collection[3] = ...
		else
		{
			array_splice($this->records, $offset, 0, array($this->markRecordAdded($value)));
		}
	}


	public function offsetExists($offset)
	{
		$this->loadRecords();
		return isset($this->records[$offset]);
	}


	public function offsetUnset($offset)
	{
		throw new BadMethodCallException('You can not remove a record from a collection');
	}


	protected function markRecordAdded($record)
	{
		$record = $this->typecast($record);

		if($record->getModel() !== $this->getModel())
		{
			$rModel = $record->getModel();
			$cModel = $this->getModel();
			throw new DifferentialModelException('The record(' . $rModel . ', entityName:' . $rModel::getEntityName() . ') has to belong to the same model as the collection(' . $cModel . ', entityName:' . $cModel::getEntityName() . ').');
		}


		# Make the record to match all the collection's querie's conditions
		$conditions = $this->getQuery()->getConditions();

		$conditions->applyToRecord($record);

		return $record;
	}


	protected function typecast($record)
	{
		if(is_array($record))
		{
			$model = $this->getModel();
			$record = $model::build($record);
		}

		return $record;
	}


	/**
	 * Track the record to be removed from the collection.
	 *
	 * @param RecordInterface $record
	 *
	 * @return RecordInterface The removed record.
	 */
	protected function markRecordRemoved($record)
	{
		$this->removedRecords[] = $record;

		return $record;
	}


	public function save()
	{
		$success = TRUE;

		foreach($this->records AS $record)
		{
			$success = $success && $record->save();
		}

		return $success;
	}


	public function destroy()
	{

		foreach($this AS $record)
		{
			if( ! $record->destroy())
			{
				return FALSE;
			}
		}

		$this->records = array();

		return FALSE;
	}


	public function isClean()
	{
		return ! $this->isDirty();
	}


	public function isDirty()
	{
		foreach($this->records AS $record)
		{
			if( ! $record->isClean())
			{
				return TRUE;
			}
		}

		if(count($this->removedRecords) > 0)
		{
			return TRUE;
		}

		return FALSE;
	}


	public function addRecord($record)
	{
		$this->records[] = $this->markRecordAdded($record);
	}


	public function removeRecord($record)
	{
		foreach($this->records AS $key => $rec)
		{
			if($record === $rec)
			{
				$this->markRecordRemoved($this->records[$key]);
				unset($this->records[$key]);
			}
		}

		# reindex the array
		$this->records = array_values($this->records);
	}


	public function setRecords($records)
	{
		$this->records = array();
		$this->markLoaded();

		foreach($records AS $rec)
		{
			# $this->records[] = $this->markRecordAdded($rec);
			$this->records[] = $rec;
		}
	}


	public function replaceRecords($records)
	{
		$newRecords = array();
		$this->markLoaded();

		foreach($records AS $rec)
		{
			$newRecords[] = $this->markRecordAdded($rec);
		}

		$oldRecords = $this->records;

		foreach($oldRecords AS $rec)
		{
			if( ! in_array($rec, $newRecords, TRUE))
			{
				$this->markRecordRemoved($rec);
			}
		}

		$this->records = $newRecords;
	}


	public function setChildCollectionFor($relationship, $collection)
	{
		$this->childCollections[$relationship->getName()] = $collection;
	}


	public function getChildCollectionFor($relationship)
	{
		return $this->childCollections[$relationship->getName()];
	}


	public function hasChildCollectionFor($relationship)
	{
		return array_key_exists($relationship->getName(), $this->childCollections);
	}


	/**
	 * Catches calls to not defined methods and:
	 * - tries to delegate to a matching model's defined scope
	 *
	 * @return CollectionInterface (fluent interface)
	 */
	public function __call($method, $params)
	{
		$model = $this->getModel();

		if($model::isScopeDefined($method))
		{
			return $this->all($model::getScope($method, $params)->getQuery());
		}
		else
		{
			$class = get_class($this);
			throw new BadMethodCallException("Called method {$class}::{$method}() does not exist.");
		}
	}

	public function __clone()
	{
		$this->query = $this->getQueryClone();
	}

	protected function getQueryClone()
	{
		if($this->query !== NULL)
		{
			return clone $this->query;
		}

		return NULL;
	}


	protected function getNewQuery($otherQuery = NULL)
	{
		if($query = $this->getQueryClone())
		{
			return $query->merge($otherQuery);
		}
		else
		{
			$model = $this->getModel();
			return $model::getNewQuery($otherQuery);
		}
	}


	public function calculate($aggregator)
	{
		return $aggregator->getValueFor($this);
	}

}
