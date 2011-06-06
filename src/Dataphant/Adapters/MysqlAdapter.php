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

class MysqlAdapter extends SqlAdapterBase
{
	/**
	 * The format of quotes in sqlite
	 */
	const QUOTES = "'";


	static protected $identifierQuotation = '`';

	/**
	 * The Sqlite default options.
	 *
	 * @var string
	 */
	static protected $defaultOptions = array(
		# By default the data is just temporarily in the memory. No file have to be created.
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => 'defaultdb'
	);


	/**
	 * The driver name to be used to initialize the PDO connection.
	 *
	 * @var string
	 */
	protected $driverName = 'mysql';


	/**
	 * The DSN string to be used for the PDO connection.
	 * Even with PDO it has a different format for each dbms
	 *
	 * @return string
	 */
	protected function getDSN()
	{
		return $this->driverName .
		       	':host=' . $this->options['hostname'] .
				';dbname=' . $this->options['database'];
	}

	public function getConnection()
	{
		if ($this->connection === NULL)
		{
			$this->connection = new PDO($this->getDSN(), $this->options['username'], $this->options['password']);
		    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->connection;
	}

}
