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

use Dataphant\Exceptions\AdapterNotFoundException;

/**
 * This is a registry holding all registered database adapters.
 */
class AdapterRegistry
{
	/**
	 * The one and only AdapterRegistry instance.
	 *
	 * @var AdapterRegistry
	 */
	static protected $singletonInstance;

	/**
	 * All Adapters registered in the AdapterRegistry.
	 *
	 * @var array
	 */
	protected $adapters = array();


	/**
	 * Get a singleton instance of the AdapterRegistry.
	 *
	 * @return AdapterRegistry the instance
	 */
	public static function getInstance()
	{
		if( ! isset(static::$singletonInstance))
		{
			static::$singletonInstance = new static();
		}

		return static::$singletonInstance;
	}

	/**
	 * Removes the singleton interface.
	 * Just for unit testing.
	 *
	 * @return void
	 */
	public static function clearInstance()
	{
		static::$singletonInstance = NULL;
	}

	/**
	 * Get a list of all registeres adapters.
	 *
	 * @return array the list of all adapters keyed by name
	 */
	public function getAllAdapters()
	{
		return $this->adapters;
	}

	/**
	 * Registers a Database Adapter.
	 *
	 * @param string $adapter the adapter object to be registered
	 * @return void
	 */
	function registerAdapter($adapter)
	{
		$this->adapters[$adapter->getName()] = $adapter;
	}

	/**
	 * Get one of the registered adapters by name.
	 *
	 * @throws AdapterNotFoundException
	 *
	 * @param string $name the adapters name
	 * @return AdapterInterface
	 */
	function getAdapter($name)
	{
		if(isset($this->adapters[$name]))
		{
			return $this->adapters[$name];
		}
		throw new AdapterNotFoundException("Adapter '{$name}' has not been registered.");
	}
}
