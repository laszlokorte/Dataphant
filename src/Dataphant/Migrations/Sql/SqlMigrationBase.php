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

use Dataphant\Migrations\MigrationInterface;

abstract class SqlMigrationBase implements MigrationInterface
{

	protected $dataSource;

	public function __construct($dataSource)
	{
		$this->dataSource = $dataSource;
	}

	public function up()
	{

	}

	public function down()
	{

	}

	protected function createTable($tableName)
	{

	}

	protected function dropTable($tableName)
	{

	}

	protected function createColumn($tableName, $columnName, $options = array())
	{

	}

	public function dropColumn($tableName, $columnName)
	{

	}

	protected function alterColumn($tableName, $columnName, $options)
    {

    }
}
