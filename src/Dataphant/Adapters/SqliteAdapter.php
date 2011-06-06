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

use PDO;

class SqliteAdapter extends SqlAdapterBase
{
	/**
	 * The format of quotes in sqlite
	 */
	const QUOTES = "'";


	/**
	 * The Sqlite default options.
	 *
	 * @var string
	 */
	static protected $defaultOptions = array(
		# By default the data is just temporarily in the memory. No file have to be created.
		'filename' => ':memory:'
	);


	/**
	 * The driver name to be used to initialize the PDO connection.
	 *
	 * @var string
	 */
	protected $driverName = 'sqlite';


	/**
	 * The DSN string to be used for the PDO connection.
	 * Even with PDO it has a different format for each dbms
	 *
	 * @return string
	 */
	protected function getDSN()
	{
		return $this->driverName .
		       ':' . $this->options['filename'];
	}

	public function getConnection()
	{
		if ($this->connection === NULL)
		{
			$this->connection = new PDO($this->getDSN());
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->connection;
	}

}
