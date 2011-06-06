<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Migrations\Sql;


/**
 * This interface provides methods for operating on the database schema.
 * One Schema manipulation is linked to one table.
 *
 * In SQL:
 * column = Column
 * Table = Table
 *
 */
class SqlSchemaManipulation implements SqlSchemaManipulationInterface
{

	protected $oldTableName;

	protected $newTableName;

	protected $columnsToDrop = array();

	protected $columnsToCreate = array();


	public function __construct($tableName)
	{
		$this->oldTableName = $tableName;
	}


	/**
	 * Set columns to be dropped of the table.
	 *
	 * @param array $columns List of Property objects.
	 *
	 * @return void
	 */
	public function setColumnsToDrop($columns)
	{

	}


	/**
	 * Get the columns to be dropped of the table.
	 *
	 * @return array List of Property objets
	 */
	public function getColumnsToDrop()
	{

	}


	/**
	 * Set properties to be created in the table.
	 *
	 * @param array $columns List of Property objects.
	 *
	 * @return void
	 */
	public function setColumnsToCreate($columns)
	{

	}


	/**
	 * Get a list of columns to be created in the table.
	 *
	 * @return array List of Property objects
	 */
	public function getColumnsToCreate()
	{

	}


	/**
	 * Set the name the table should be renamed to.
	 *
	 * @param string $name The new name.
	 *
	 * @return void
	 */
	public function setNewTableName($name)
	{

	}


	/**
	 * Get the name the table should be renamed to.
	 * Returns NULL if the table should not be renamed.
	 *
	 * @return string the new name
	 */
	public function getNewTableName()
	{

	}


	/**
	 * Get the old table's name.
	 * Returns NULL if the table did not exist before.
	 *
	 * @return strings The old tables name.
	 */
	public function getOldTableName()
	{

	}


	/**
	 * Set the table to be dropped.
	 *
	 * @return void
	 */
	public function dropTable()
	{

	}


	/**
	 * Check if the table to be dropped.
	 *
	 * @return void
	 */
	public function isSetToBeDropped()
	{

	}


	/**
	 * Get a new schema manipulation to undo the current one.
	 *
	 * @return void
	 */
	public function reverse()
	{

	}

}