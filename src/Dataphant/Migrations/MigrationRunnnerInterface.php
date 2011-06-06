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


interface MigrationRunnerInterface
{

	/**
	 * Run all migrations up to the given $version.
	 * When no $version is specified, all migrations will be run.
	 *
	 * @param integer $level
	 *
	 * @return void
	 */
	public function migrateUp($version);


	/**
	 * Run all migrations down to the given $version.
	 * If no $version is specified just one migration will be run.
	 *
	 * @param integer $version
	 *
	 * @return void
	 */
	public function migrateDown($version);

}