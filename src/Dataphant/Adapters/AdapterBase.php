<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Adapters;

use Dataphant\Query\Query;
use Dataphant\Utils\Inflector;

/**
 * The AdapterBase provides database independent base functionality.
 */
abstract class AdapterBase implements AdapterInterface
{

	static protected $defaultOptions = array(
	);

	/**
	 * Additions options the adapter is cutomized with.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Name of our adapter.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Is the Debugmode enabled?
	 *
	 * @var string
	 */
	protected $debugMode = FALSE;

	/**
	 * the latest statement flushed to the database.
	 *
	 * @var string
	 */
	protected $lastStatement;

	/**
	 * Name of the used driver.
	 *
	 * @var string
	 */
	protected $driverName;

	/**
	 * Connection reference to used database.
	 *
	 * @var PDOInstance
	 */
	protected $connection = NULL;

	/**
	 * Build a new adapter for establising a database connection
	 *
	 * eg $sql = new MySqlAdapter('default', array('hoste' => 'localhost', 'username' => 'root', 'password' => 'secret', 'database' => 'production_db'))
	 *
	 * @param string $name    The adapters name.
	 * @param string $options Cutomizations eg. the database auth data.
	 */
	public function __construct($name, $options = array())
	{
		$this->name = $name;

		$this->options = array_merge(static::$defaultOptions, $options);

		if(isset($options['inflector']))
		{
			$this->setInflector($options['inflector']);
		}
	}


	/**
	 * Get the name of the adapter.
	 *
	 * @return string The adapter's name.
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Return new Query instance to work with.
	 *
	 * @param object $dataSource The name of correspondent data source.
	 * @param object $model      Model to link query to.
	 * @param array  $options    Options to pass to the query.
	 *
	 * @return QueryInterface A new Query object.
	 */
	public function getNewQuery($dataSource, $model, $options=array())
	{
		$newQuery = new Query($dataSource, $model, $options);
		return $newQuery;
	}


	/**
	 * Enable the debug mode.
	 * In the debug mode all queries made are logged but they are not executed on the real data.
	 *
	 * @param string $bool
	 *
	 * @return void
	 */
	public function setDebugMode($bool)
	{
		$this->debugMode = (bool)$bool;
	}


	/**
	 * Check if the debug mode is enabled.
	 *
	 * @return boolean If debug mode is enabled.
	 */
	public function getDebugMode()
	{
		return $this->debugMode;
	}


	/**
	 * Get the last statement the adapter has been told to execute.
	 * This works also in the debug mode.
	 *
	 * @return string
	 */
	public function getLastStatement()
	{
		return $this->lastStatement;
	}


	/**
	 * Convert the given value into a format that is for sure not interpreted as command in the underlying database.
	 * For Sql databases this set strings into quotes add escape characters in front of all quotes inside the value.
	 *
	 * @param mixed $value
	 *
	 * @return String the converted value
	 */
	public function quote($value)
	{
		// '123' validates as numeric but should be handled as string
		if(is_numeric($value) && !is_string($value))
		{
			return $value;
		}
		elseif(is_null($value))
		{
			return 'NULL';
		}
		else
		{
			return $this->getConnection()->quote($value);
		}
	}


	/**
	 * This escapes the given string by putting escape character in front of
	 * all characters which would be interpreted as end of string inside the
	 * underlying database.
	 *
	 * @param string $string
	 *
	 * @return string The escaped string
	 */
	protected function escape_string($string)
	{
		return sqlite_escape_string($string);
	}


	/**
	 * Remember the given statement as the last one executed.
	 *
	 * @param string $stm The statement string to remember.
	 *
	 * @return void
	 */
	protected function setLastStatement($stm)
	{
		$this->lastStatement = $stm;
	}


	public function getInflector()
	{
		if( ! isset($this->inflector))
		{
			$this->inflector = Inflector::getInstance();
		}
		return $this->inflector;
	}


	public function setInflector($inflector)
	{
		$this->inflector = $inflector;
	}

	abstract public function getConnection();
}
