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
 */
interface SqlSchemaManipulationInterface
{

	/**
	 * Set fields to be dropped of the table.
	 *
	 * @param array $fields List of Property objects.
	 *
	 * @return void
	 */
	public function setColumnsToDrop($fields);


	/**
	 * Get the fields to be dropped of the table.
	 *
	 * @return array List of Property objets
	 */
	public function getColumnsToDrop();


	/**
	 * Set properties to be created in the table.
	 *
	 * @param array $fields List of Property objects.
	 *
	 * @return void
	 */
	public function setColumnsToCreate($fields);


	/**
	 * Get a list of fields to be created in the table.
	 *
	 * @return array List of Property objects
	 */
	public function getColumnsToCreate();


	/**
	 * Set the name the table should be renamed to.
	 *
	 * @param string $name The new name.
	 *
	 * @return void
	 */
	public function setTableName($name);


	/**
	 * Get the name the table should be renamed to.
	 * Returns NULL if the table should not be renamed.
	 *
	 * @return string the new name
	 */
	public function getNewTableName();


	/**
	 * Get the old table's name.
	 * Returns NULL if the table did not exist before.
	 *
	 * @return strings The old tables name.
	 */
	public function getOldTableName();


	/**
	 * Set the table to be dropped.
	 *
	 * @return void
	 */
	public function dropTable();


	/**
	 * Check if the table to be dropped.
	 *
	 * @return void
	 */
	public function isSetToDropTable();


	/**
	 * Get a new schema manipulation to undo the current one.
	 *
	 * @return void
	 */
	public function reverse();

}