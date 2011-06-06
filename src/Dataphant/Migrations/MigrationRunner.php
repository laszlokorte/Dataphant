<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Migrations;


class MigrationRunner
{

	protected $migrations = array();

	protected $options = array();

	public function __construct($migrations, $options)
	{
		$this->migrations = $migrations;

		$this->options = $options;
	}


	/**
	 * Run all migrations up to the given $version.
	 * When no $version is specified, all migrations will be run.
	 *
	 * @param integer $level
	 *
	 * @return void
	 */
	public function migrateUp($version)
	{
		foreach($this->migrations AS $migration)
		{
			$migration->performUpgrade();
		}
	}


	/**
	 * Run all migrations down to the given $version.
	 * If no $version is specified just one migration will be run.
	 *
	 * @param integer $version
	 *
	 * @return void
	 */
	public function migrateDown($version)
	{
		foreach($this->migrations AS $migration)
		{
			$migration->performDowngrade();
		}
	}

}