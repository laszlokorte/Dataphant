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
use Exception;

use Dataphant\Utils\LoggerInterface;

use Dataphant\Query\Query;
use Dataphant\Query\Path;
use Dataphant\Query\Operations\NullOperation;
use Dataphant\Query\PathInterface;

use Dataphant\Migrations\SchematicDatabaseInterface;
use Dataphant\Properties\PropertyInterface;
use Dataphant\Query\Comparisons\ComparisonInterface;
use Dataphant\Query\Operations\NotOperation;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Query\Aggregators\AggregatorInterface;

use Dataphant\Exceptions\UnsufficientAdapterSupportException;

/**
 * This is the base class for all sql RDBMS database adapters
 *
 * @package default
 */
abstract class SqlAdapterBase extends AdapterBase implements SchematicDatabaseInterface
{

	/**
	 * List property types mapped to the according datatype of the database.
	 * This is used to create the database schema from a given model definition.
	 *
	 * @var array
	 */
	static protected $types = array(
		'Dataphant\Properties\BinaryProperty' => 'BINARY',
		'Dataphant\Properties\BooleanProperty' => 'BOOLEAN',
		'Dataphant\Properties\DateProperty' => 'DATE',
		'Dataphant\Properties\DateTimeProperty' => 'DATETIME',
		'Dataphant\Properties\DecimalProperty' => 'DECIMAL',
		'Dataphant\Properties\FloatProperty' => 'FLOAT',
		'Dataphant\Properties\IntegerProperty' => 'INTEGER',
		'Dataphant\Properties\SerialProperty' => 'INTEGER',
		'Dataphant\Properties\StringProperty' => 'VARCHAR',
		'Dataphant\Properties\TextProperty' => 'TEXT',
		'Dataphant\Properties\TimeProperty' => 'TIME',
	);

	/**
	 * List of comparison types mapped to the according comparison operator of the database.
	 * This is used to convert Comparison object into an statement string
	 *
	 * @var array
	 */
	static protected $comparisons = array(
		'Dataphant\Query\Comparisons\EqualToComparison' => ' = ',
		'Dataphant\Query\Comparisons\LessThanOrEqualToComparison' => ' <= ',
		'Dataphant\Query\Comparisons\LessThanComparison' => ' < ',
		'Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison' => ' >= ',
		'Dataphant\Query\Comparisons\GreaterThanComparison' => ' > ',
		'Dataphant\Query\Comparisons\LikeComparison' => ' LIKE ',
		'Dataphant\Query\Comparisons\InEnumComparison' => ' IN '
	);


	static protected $aggregators = array(
		'Dataphant\\Query\\Aggregators\\CountAggregator' => 'COUNT',
		'Dataphant\\Query\\Aggregators\\MaximumAggregator' => 'MAX',
		'Dataphant\\Query\\Aggregators\\MinimumAggregator' => 'MIN',
		'Dataphant\\Query\\Aggregators\\AverageAggregator' => 'AVG',
		'Dataphant\\Query\\Aggregators\\SumAggregator' => 'SUM',
	);


	/**
	 * The default comlumn type to use when a property is not supported natively by the database
	 *
	 * @var string
	 */
	static protected $defaultType = 'VARCHAR';


	/**
	 * Character to be used to quote idenfier names like table or columnnames
	 *
	 * @var string
	 */
	static protected $identifierQuotation = '"';


	static protected $trueExpression = '1';


	/**
	 * Character to be used to seperate multiple identifier in a path.
	 * Eg database.table.column
	 *
	 * @var string
	 */
	static protected $identifierSeparator = '.';


	/**
	 * Character to be used to separate composed identifiers.
	 * Eg used to prefix tablenames or aliases: csp_users
	 *
	 * @var string
	 */
	static protected $prefixSeparator = '_';


	/**
	 * Character to be used to separate lists of identifiers or values
	 * Eg INSERT INTO  table (col1, col2, col3) VALUES (val1, val2, val3)
	 *
	 * @var string
	 */
	static protected $listSeparator = ', ';


	/**
	 * Generate the DSN string being used by PDO to establish the database connection.
	 *
	 * @return string The DSN string
	 */
	protected function getDSN()
	{
		return $this->driverName .
		       ':host=' . $this->options['hostname'] .
		       ';port=' . $this->options['port'] .
		       ';dbname=' . $this->options['dbname'];
	}


	/**
	 * Get the PDO object which is connected to the database.
	 * The connection is lazy so it get established only when it is access first time.
	 *
	 * @return PDO
	 */
	public function getConnection()
	{
		if ($this->connection === NULL)
		{
			$this->connection = new PDO($this->getDSN(), $this->options['username'], $this->options['password']);
		    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->connection;
	}


	/**
	 * Insert the values given by the bindings array into the $statement string after quoting them.
	 * Check out the documentation of AdapterInterface#execute() for more information.
	 *
	 * @param string $statement
	 * @param array $bindings
	 *
	 * @return string The generated statement
	 */
	protected function bindParams($statement, $bindings = array())
	{
		foreach($bindings AS $key => $bind)
		{
			if(is_int($key))
			{
				$statement = preg_replace("/([^\\\\][\\\\\\\\]*)\\?/i", '$1' . $this->quote($bind), $statement, 1);
				$key++;
			}
			$statement = preg_replace("/([^\\\\][\\\\\\\\]*)\:{$key}/i", '$1' . $this->quote($bind), $statement, 1);

		}

		return $statement;
	}


	public function execute($statement, $bindings = array())
	{
		$statement = $this->bindParams($statement, $bindings);
		$this->setLastStatement($statement);

		if ( ! $this->getDebugMode())
		{
			$connection = $this->getConnection();
			$time = microtime(TRUE);
			$result = $connection->query($statement, PDO::FETCH_ASSOC);
			$time = (microtime(TRUE) - $time) * 1000;

			$this->log(LoggerInterface::INFO, sprintf("SQL: (%.5fms) %s", $time, $statement));
		}
		else
		{
			$result = FALSE;
		}
		return $result;
	}

	protected function getLogger()
	{
		if( ! isset($this->logger))
		{
			$this->logger = isset($this->options['logger']) ? $this->options['logger'] : NULL;
		}
		return $this->logger;
	}

	protected function log($level, $msg)
	{
		$logger = $this->getLogger();

		if($logger !== NULL)
		{
			$logger->log($level, $msg);
		}
	}

	public function create($records)
	{
		# TODO: need to be tested!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		foreach($records AS $record)
		{
			$model = $record->getModel();
			$serial = $model::getSerial();
			$dirtyAttributes = $record->getDirtyAttributes();
			$allProperties = $model::getProperties();

			$properties = array();
			$values = array();

			foreach($allProperties AS $key => $prop)
			{

				if( ! array_key_exists($key, $dirtyAttributes))
				{
					continue;
				}

				$value = $dirtyAttributes[$key];

				if($value === NULL && ($prop->isSerial() || ! $prop->hasDefaultValue()))
				{
					continue;
				}

				if($prop === $serial)
				{
					$serial = NULL;
				}

				$properties[] = $prop;
				$values[] = $value;
			}

			$result = $this->execute($this->insertStatement($model, $properties, $values, $serial));

			if($result && $result->rowCount() === 1 && $serial !== NULL)
			{
				$serial->setValueFor($record, $serial->unserialize($this->connection->lastInsertId()));
			}
		}
	}


	public function read($query)
	{
		$result = $this->execute($this->selectStatement($query));

		return $result;
	}


	public function update($attributes, $collection)
	{
		$query = $collection->getQuery();
		$model = $query->getModel();
		$allProperties = $model::getProperties();

		$properties = array();
		$values = array();
		foreach($allProperties AS $key => $property)
		{
			if(isset($attributes[$key]))
			{
				$properties[] = $property;
				$values[] = $attributes[$key];
			}
		}
		return $this->execute($this->updateStatement($properties, $values, $query));
	}


	public function delete($collection)
	{
		$query = $collection->getQuery();

		return $this->execute($this->deleteStatement($query));
	}


	public function aggregate($query)
	{
		$result = $this->execute($this->selectStatement($query));

		return $result;
	}


	public function getNewSchemaDefinition()
	{

	}


	public function applySchemaDefinition($schemaDefinition)
	{

	}


	public function createDataSchema($model)
	{
		return $this->execute($this->createTableStatement($model));
	}


	public function dropDataSchema($model)
	{

	}


	/**
	 * Generates a statement for creating the table for the given model.
	 *
	 * @param string $model The model's class name
	 *
	 * @return string The CREATE TABLE statement
	 */
	protected function createTableStatement($model)
	{
		$keys = $model::getKeys();
		$properties = array_merge($keys, $model::getProperties());

		$tableName = $this->tableName($model, FALSE);

		$columns = array();
		foreach($properties AS $property)
		{
			$columns[] = $this->columnDeclarationStatement($property);
		}
		$columnList = join(static::$listSeparator, $columns);

		$primaryKeyConstraint = $this->primaryKeyConstraintStatement($tableName, $keys);

		$statement = 'CREATE TABLE ' . $this->quoteIdentifier($tableName);
		$statement .= ' (';
		$statement .= $columnList;
		if( ! empty($primaryKeyConstraint))
		{
			$statement .= static::$listSeparator . $primaryKeyConstraint;
		}
		$statement .= ')';
		return $statement;
	}


	/**
	 * Generate the statement to drop a model's table.
	 *
	 * @param string $model The model's class name
	 *
	 * @return string The DROP TABLE statement
	 */
	protected function dropTableStatement($model)
	{
		$statement = 'DROP TABLE ' . $this->tableName($model);

		return $statement;
	}


	/**
	 * Generate the statement for creating primary key constraints on a given table.
	 *
	 * eg:
	 * CONTRAINT users_PK PRIMARY KEY (id)
	 *
	 * @param string $table The table name
	 * @param array $keyProperties List of properties to be the primary keys
	 *
	 * @return string
	 */
	protected function primaryKeyConstraintStatement($table, $keyProperties)
	{
		$keyList = join(static::$listSeparator, array_map(function($property){
			return $property->getFieldName();
		}, $keyProperties));

		$pkName = $this->quoteIdentifier($table . static::$prefixSeparator . 'PK');

		return 'CONSTRAINT ' . $pkName . ' PRIMARY KEY (' . $keyList . ')';
	}


	/**
	 * Get the statement to declare a tables column inside a CREATE TABLE statement.
	 *
	 * eg:
	 * username VARCHAR(50)
	 *
	 * @param PropertyInterface $property The property to be stored inside the column.
	 *
	 * @return string
	 */
	protected function columnDeclarationStatement($property)
	{
		$fieldname = $this->quoteIdentifier($property->getFieldName());
		$type = $this->columnTypeStatement($property);

		return "{$fieldname} {$type}";
	}


	/**
	 * Get the statement to declare the column type.
	 *
	 * eg:
	 * VARCHAR(50)
	 *
	 * @param PropertyInterface $property The property to be stored in the column.
	 *
	 * @return string
	 */
	protected function columnTypeStatement($property)
	{
		if(isset(static::$types[get_class($property)]))
		{
			$statement = static::$types[get_class($property)];
			if($property->getLength() > 0)
			{
				$statement .= '(' . $property->getLength() . ')';
			}
		}
		else {
			$statement = static::$defaultType;
		}

		return $statement;
	}


	/**
	 * Generate a SELECT statement for the given query.
	 *
	 * eg:
	 * SELECT users.nickname AS nickname, users.age AS age FROM users WHERE ((users.id = 5))
	 *
	 * @param QueryInterface $query
	 *
	 * @return string
	 */
	protected function selectStatement($query)
	{
		$model = $query->getModel();
		$fieldList = $query->getFields();

		$fields = $this->fieldListWithAlias($fieldList);
		$tableName = $this->tableName($query->getModel());
		$condition = $this->operationStatement($query->getConditions());
		$groupBy = $query->toBeUnique() ? $this->groupByStatement($query->getFields()) : '';
		$order = $this->orderListStatement($query->getOrder());
		$limit_offset = $this->limitOffsetStatement($query->getLimit(), $query->getOffset());
		$join = $this->joinStatement($query);

		$statement = "SELECT {$fields} FROM {$tableName}{$join} WHERE {$condition}{$groupBy}{$order}{$limit_offset}";

		return $statement;
	}


	/**
	 * Generate INSERT statement for inserting the given $values for the
	 * given $properties into the table for the given $model.
	 *
	 * eg:
	 * INSERT INTO users (nickname, age) VALUES ('Jack', 42)
	 *
	 * @param string $model The model's class name.
	 * @param array $properties The list of properties whose values should be set
	 * @param array $values The list of values
	 * @param ProperyInterface $serial The serial property. Needed for dbms like postgrSql where a RETURNING is needed to the the serial's value
	 *
	 * @return string The INSERT statement
	 */
	protected function insertStatement($model, $properties, $values, $serial)
	{
		$tableName = $this->tableName($model);
		$fields = $this->fieldList($properties, FALSE);
		$values = $this->valueList($values);

		if(!empty($fields))
		{
			$statement = "INSERT INTO {$tableName} ({$fields}) VALUES ({$values})";
		}
		else
		{
			$statement = "INSERT INTO {$tableName} DEFAULT VALUES";
		}

		return $statement;
	}


	/**
	 * Generate an UPDATE statement for setting the given $properties to the given $values for all
	 * records matching the given $query.
	 *
	 * eg UPDATE users SET nickname = 'Sawyer', state = 'lost' WHERE ((id = 16))
	 *
	 * @param array $properties The properties which columns should be updated.
	 * @param array $values The values to set the columns to.
	 * @param QueryInterface $query The query containing the conditions.
	 *
	 * @return string The UPDATE statement.
	 */
	protected function updateStatement($properties, $values, $query)
	{
		$tableName = $this->tableName($query->getModel());
		$condition = $this->operationStatement($query->getConditions());
		$fieldAssignments = $this->fieldAssignmentList($properties, $values);

		$statement = "UPDATE {$tableName} SET {$fieldAssignments} WHERE {$condition}";

		return $statement;
	}


	/**
	 * Generate a DELETE statement for removing all records matching the given $query's conditions.
	 *
	 * @param QueryInterface $query
	 *
	 * DELETE users WHERE ((id=23))
	 *
	 * @return sting the UPDATE statement
	 */
	protected function deleteStatement($query)
	{
		$tableName = $this->tableName($query->getModel());
		$condition = $this->operationStatement($query->getConditions());

		$statement = "DELETE FROM {$tableName} WHERE {$condition}";

		return $statement;
	}


	/**
	 * Generate an statement string for the given operation.
	 *
	 * eg
	 * ((users.nickname = 'Jacob') AND (users.password = 'secret'))
	 *
	 * @param OperationInterface $operation The operation to convert into a statement string.
	 *
	 * @return string
	 */
	protected function operationStatement($operation)
	{
		$operations = array(
			'Dataphant\Query\Operations\AndOperation' => ' AND ',
			'Dataphant\Query\Operations\OrOperation' => ' OR '
		);

		if ($operation instanceof ComparisonInterface)
		{
			return $this->comparisonStatement($operation);
		}
		elseif($operation instanceof NullOperation)
		{
			return static::$trueExpression;
		}

		$members = array();
		foreach ($operation AS $operand)
		{
			$members[] = $this->operationStatement($operand);
		}

		if($operation instanceof NotOperation)
		{
			return '(NOT ' . reset($members) . ')';
		}
		else
		{
			return '(' . join($members, $operations[get_class($operation)]) . ')';
		}

	}


	/**
	 * Generate a comparison statement for the given comparison object.
	 *
	 * eg
	 * (users.age > 18)
	 *
	 * @param ComparisonInterface $comparison
	 *
	 * @return string
	 */
	protected function comparisonStatement($comparison)
	{
		if ($comparison instanceof InEnumComparison && count($comparison->getValue()) < 1)
		{
			return 'NULL';
		}
		elseif($comparison->isComparingRelationship())
		{
			$relationship = $comparison->getSubject();
			$value = $comparison->getValue();

			$targetKeys = $relationship->getInverse()->getTargetKeys();

			foreach($targetKeys AS $k => $v)
			{
				$targetKeys[$k] = new Path(array($relationship->getInverse()), $v->getName());
			}

			$conditions = Query::targetConditions($value, $relationship->getSourceKeys(), $targetKeys);

			return $this->operationStatement($conditions);
		}


		return '(' . $this->operand($comparison->getSubject()) . static::$comparisons[get_class($comparison)] . $this->operand($comparison->getValue()) . ')';

	}


	/**
	 * Get a statement for assigning a list of $values to the columns of a list of $properties.
	 *
	 * eg
	 * nickname = 'Peter', age = 42
	 *
	 * @param array $properties
	 * @param array $values
	 *
	 * @return string
	 */
	protected function fieldAssignmentList($properties, $values)
	{
		$assignments = array();

		for($i=0,$j=count($properties); $i<$j; $i++)
		{
			$assignments[] = $this->fieldAssignmentStatement($properties[$i], $values[$i]);
		}

		return join(static::$listSeparator, $assignments);
	}


	/**
	 * Get a statement for assining a given $value to a given $property's column.
	 *
	 * eg
	 * nickname = 'Walter'
	 *
	 * @param PropertyInterface $property
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function fieldAssignmentStatement($property, $value)
	{
		return $this->fieldName($property, FALSE) . '=' . $this->operand($property->serialize($value));
	}


	protected function joinStatement($query)
	{

		$statements = array('');

		$links = $query->getLinks();
		$sourceAlias = $this->tableName($query->getModel(), FALSE);


		$links = array_reverse($links);

		foreach($links AS $link)
		{
			$statements[] = 'INNER JOIN';

			$targetAlias = $this->tableName($link->getTargetModel(), FALSE);
			$statements[] = $this->quoteIdentifier($targetAlias);
			$statements[] = 'AS';
			$targetAlias = $link->getInverse()->getName() . static::$prefixSeparator . $targetAlias;
			$statements[] = $this->quoteIdentifier($targetAlias);

			$statements[] = 'ON';
			$statements[] = $this->joinConditionsStatement($link, $sourceAlias, $targetAlias);

			$sourceAlias = $targetAlias;
		}

		return join(' ', $statements);
	}

	protected function joinConditionsStatement($link, $sourceAlias, $targetAlias)
	{
		$statements = array();
		$sourceKeys = $link->getSourceKeys();
		$targetKeys = $link->getTargetKeys();

		foreach($sourceKeys AS $sourceKey)
		{
			$statements[] = '(' . $this->fieldName(current($targetKeys), $targetAlias) . ' = ' . $this->fieldName($sourceKey, $sourceAlias) . ')';
			next($targetKeys);
		}

		return join(' AND ', $statements);
	}

	/**
	 * Join the given values into a statement to be used as value list in an UPDATE statement.
	 *
	 * eg
	 * ('Ben', 30, 'leader')
	 *
	 * @param array $values
	 *
	 * @return string
	 */
	protected function valueList($values)
	{
		return join(static::$listSeparator, array_map(array($this, 'operand'), $values));
	}


	/**
	 * Convert a given operand of an assignment or comparison statement into the right format.
	 * If the operand is a scalar it gets quoted.
	 * If the value is an Property or a Path it gets converted into the field's name.
	 *
	 * eg
	 * 'John'
	 * or
	 * users.nickname
	 *
	 * @param mixed $val
	 *
	 * @return string
	 */
	protected function operand($val)
	{
		if(is_scalar($val))
		{
			return $this->quote($val);
		}
		elseif($val instanceof PropertyInterface || $val instanceof PathInterface)
		{
			return $this->fieldName($val, TRUE);
		}
		elseif(is_array($val) || $val instanceof Traversable)
		{
			$list = array();
			foreach($val AS $v)
			{
				$list[] = $this->operand($v);
			}

			if(count($list) === 0)
			{
				return '';
			}
			else
			{
				return '(' . join(static::$listSeparator, $list) . ')';
			}
		}
	}


	/**
	 * Generate a GROUP BY statement for the given list of fields.
	 * A field can either be an operation or a property.
	 * The generated GROUP BY statement contains just a list of the properties.
	 *
	 * eg
	 * GROUP BY users.nickname, users.age
	 *
	 * @param array $fields
	 *
	 * @return string
	 */
	protected function groupByStatement($fields)
	{
		foreach($fields AS $key => $f)
		{
			if( ! $f instanceof PropertyInterface)
			{
				unset($fields[$key]);
			}
		}
		if(count($fields) > 0)
		{
			return ' GROUP BY (' . $this->fieldList($fields) . ')';
		}

		return '';
	}


	/**
	 * Generate an ORDER BY statement for the given list of $orders
	 *
	 * eg
	 * ORDER BY users.nickname DESC, users.age
	 *
	 * @param array $orders A list of OrderInterface objects
	 *
	 * @return string
	 */
	protected function orderListStatement($orders)
	{
		if( ! empty($orders))
		{
			return ' ORDER BY ' . join(static::$listSeparator, array_map(array($this, 'orderStatement'), $orders));
		}
	}

	/**
	 * Generate a single order statement for the given $order object
	 *
	 * eg
	 * users.age DESC
	 *
	 * @param OrderInterface $order
	 *
	 * @return string
	 */
	protected function orderStatement($order)
	{
		return $this->fieldName($order->getProperty()) . ' ' . strtoupper($order->getDirection());
	}


	/**
	 * Generate a LIMIT, OFFSET statement for the given $limit and $offset
	 *
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return string
	 */
	protected function limitOffsetStatement($limit, $offset)
	{
		$return = '';
		if($limit)
		{
			$return .=  " LIMIT {$limit}";
		}
		if($offset)
		{
			if( ! $limit)
			{
				$return .=  " LIMIT -1";
			}
			$return .= " OFFSET {$offset}";
		}

		return $return;
	}


	/**
	 * Generate a list of aliased fieldnames.
	 *
	 * eg
	 * users.name AS name, users.age AS age
	 *
	 * @param array $fields Mixed list of Properties and Operations
	 *
	 * @return string
	 */
	protected function fieldListWithAlias($fields)
	{
		return join(static::$listSeparator, array_map(array($this, 'fieldNameWithAlias'), $fields));
	}


	/**
	 * Get a list of fieldnames either prefixed with their table's name or not.
	 *
	 * eg
	 * nickname, age, password
	 * or
	 * users.nickname, users.age, users.password
	 *
	 * @param array $fields List of Properties or Operations
	 * @param boolean $withTableName Set if the fieldnames should be prefixed with their table's name
	 *
	 * @return string
	 */
	protected function fieldList($fields, $withTableName = TRUE)
	{
		return join(static::$listSeparator, array_map(array($this, 'fieldName'), $fields, array($withTableName)));
	}


	/**
	 * Get a single field's name prefixed with it's table's name and aliased with just the field's name.
	 *
	 * @param mixed $field The Property or Operation object
	 *
	 * @return string
	 */
	protected function fieldNameWithAlias($field)
	{
		if($field instanceof AggregatorInterface)
		{
			return $this->aggregationWithAlias($field);
		}
		else
		{
			return $this->fieldName($field) . ' AS ' . $this->quoteIdentifier($field->getName());
		}
	}


	/**
	 * undocumented function
	 *
	 * @param string $aggregator
	 *
	 * @return void
	 */
	protected function aggregationWithAlias($aggregator)
	{
		return $this->aggregationStatement($aggregator) . ' AS ' . $this->quoteIdentifier($aggregator->getAliasName());
	}


	protected function aggregationStatement($aggregator)
	{
		return $this->aggregationOperator($aggregator) . '(' . $this->fieldName($aggregator->getProperty()) . ')';
	}


	protected function aggregationOperator($aggregator)
	{
		$aggregatorClass = get_class($aggregator);
		if(array_key_exists($aggregatorClass, static::$aggregators))
		{
			return static::$aggregators[$aggregatorClass];
		}
		else
		{
			throw new UnsufficientAdapterSupportException('Unknown aggregator "' . $aggregatorClass . '".');
		}
	}


	/**
	 * Get a single field's name prefixed with it's table name or not.
	 *
	 * @param mixed $field The Property or Operation to get the name of
	 * @param boolean,string $withTableName
	 *
	 * @return string
	 */
	protected function fieldName($field, $withTableName = TRUE)
	{
		if(!$field) return;

		$fieldName = $field->getFieldName();
		$tableName = '';
		if($withTableName)
		{

			if($withTableName===TRUE)
			{
				$model = $field->getModel();
				$tableName = $this->tableName($model, FALSE);

				if($field instanceof PathInterface)
				{
					$tableName = $field->getLastRelationship()->getInverse()->getName() . static::$prefixSeparator . $tableName;
				}

				$tableName = $this->quoteIdentifier($tableName) . static::$identifierSeparator;
			}
			elseif(is_string($withTableName))
			{
				$tableName = $this->quoteIdentifier($withTableName) . static::$identifierSeparator;
			}
		}

		return $tableName . $this->quoteIdentifier($fieldName);
	}


	/**
	 * Get the table's name of the given $model
	 *
	 * @param string $model The model's class name
	 *
	 * @return string
	 */
	protected function tableName($model, $quoted = TRUE)
	{
		if(isset($this->options['prefix']))
		{
			$tableName = $this->options['prefix'] . static::$prefixSeparator . $this->entityToTableName($model::getEntityName());
		}
		else
		{
			$tableName = $this->entityToTableName($model::getEntityName());
		}
		return $quoted === TRUE ? $this->quoteIdentifier($tableName) : $tableName;
	}


	/**
	 * Quote the given name to make sure it's interpreted as indentifier name
	 * eg:
	 * users -> "users"
	 * users"favouries -> "users""favourites"
	 *
	 * http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
	 *
	 * @param string $identifier
	 *
	 * @return void
	 */
	protected function quoteIdentifier($identifier)
	{
		$iQ = static::$identifierQuotation;
		return $iQ . str_replace($iQ, $iQ.$iQ, $identifier) . $iQ;
	}


	protected function entityToTableName($entityName)
	{
		$tableName = $this->getInflector()->pluralize($entityName);

		return $this->getInflector()->underscore($tableName);
	}

}
