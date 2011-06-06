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

use Dataphant\Adapters\AdapterInterface;

class DataSource implements DataSourceInterface
{
	const DEFAULT_NAME = 'default';

	static protected $dataSources = array();

	/**
	 * The datasources name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The list of indentityMap objects (one per model)
	 *
	 * @var array
	 */
	protected $indentityMaps = array();


	/**
	 * The adapter the DataSource uses
	 *
	 * @var AdapterInterface
	 */
	protected $adapter;

	static public function getByName($name)
	{
		if(! isset(static::$dataSources[$name]))
		{
			static::$dataSources[$name] = new static($name);
		}
		return static::$dataSources[$name];
	}

	static public function resetByName($name)
	{
		unset(static::$dataSources[$name]);
	}

	/**
	 * use getByName($name) instead
	 *
	 * @param string $name the datasources name
	 */
	protected function __construct($name)
	{
		$this->name = $name;
	}


	public function create($records)
	{
		$this->getAdapter()->create($records);
	}


	public function read($query, $asArray = FALSE)
	{
		$model = $query->getModel();
		$res = $this->getAdapter()->read($query);
		$resultArray = array();

		foreach($res AS $r)
		{
			$resultArray[] = $r;
		}

		if($asArray === TRUE) {
			return $resultArray;
		}
		else
		{
			return $model::map($resultArray, $query);
		}
	}


	public function update($attributes, $collection)
	{
		if(count($attributes) > 0)
		{
			return $this->getAdapter()->update($attributes, $collection);
		}
		else
		{
			return 0;
		}
	}


	public function delete($collection)
	{
		$this->getAdapter()->delete($collection);
	}


	public function aggregate($query)
	{
		$result = $this->getAdapter()->aggregate($query);
		$resultArray = array();

		foreach($result AS $r)
		{
			$resultArray[] = $r;
		}

		return $resultArray;
	}


	public function getNewQuery($model, $options = array())
	{
		return $this->getAdapter()->getNewQuery($this, $model, $options);
	}


	public function getIdentityMap($modelName)
	{
		if( ! isset($this->identityMaps[$modelName]))
		{
			$this->identityMaps[$modelName] = new IdentityMap();
		}

		return $this->identityMaps[$modelName];
	}


	public function getAdapter()
	{
		if( ! isset($this->adapter))
		{
			$this->adapter = AdapterRegistry::getInstance()->getAdapter($this->name);
		}

		return $this->adapter;
	}

	public function getName()
	{
		return $this->name;
	}

}
