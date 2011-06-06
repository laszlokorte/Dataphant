<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query;

use Traversable;
use Exception;

use Dataphant\Query\PathInterface;

use Dataphant\Query\Comparisons\ComparisonInterface;
use Dataphant\Query\Operations\OperationInterface;

use Dataphant\Query\Operations\NullOperation;
use Dataphant\Query\Operations\AndOperation;
use Dataphant\Query\Operations\OrOperation;

use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Relationships\RelationshipInterface;
use Dataphant\Relationships\ManyToManyRelationship;

use Dataphant\Query\Exceptions\InvalidOffsetException;
use Dataphant\Query\Exceptions\InvalidLimitException;
use Dataphant\Query\Exceptions\InvalidOrderException;
use Dataphant\Query\Exceptions\InvalidRelationshipException;

use Dataphant\Query\Aggregators\AggregatorInterface;

use Dataphant\Properties\PropertyInterface;
use Dataphant\Query\OrderInterface;

use Dataphant\Utils\ArrayTools;

use Dataphant\DataSource;

use InvalidArgumentException;
use BadMethodCallException;
use Dataphant\Exceptions\UndefinedPropertyException;

class Query implements QueryInterface
{

	/**
	 * the dataSource the query is pointed to
	 *
	 * @var DataSourceInterface
	 */
	protected $dataSourceName;


	/**
	 * The model the query is pointed to
	 * This determines the table the records come from and the class the records will be instanciated with
	 *
	 * @var string
	 */
	protected $model;


	/**
	 * The fields to be selected by the query
	 * By default these are all eager loading fields of the model.
	 * But it can be changed when needed
	 * You can only select fields from the models table -> joins are not allowed
	 *
	 * @var array of PropertyInterface|OperatorInterface objects
	 */
	protected $fields = NULL;


	/**
	 * The tables the Query should join
	 * They are stored as relation objects but they are not made to query data but just
	 * for building complex conditions
	 *
	 * @var array of RelationInterface objects
	 */
	protected $links = array();


	/**
	 * comparsion object or operation object to define the conditions
	 * the records select by the query have to match
	 *
	 * @var ConditionInterface
	 */
	protected $conditions;


	/**
	 * the number of records to skip when fetching
	 *
	 * @var integer
	 */
	protected $offset = 0;


	/**
	 * the maximum amount of records to fetch
	 *
	 * @var integer
	 */
	protected $limit = NULL;


	/**
	 * a list of order objects to be used for sorting the fetched records
	 *
	 * @var array
	 */
	protected $order = array();


	/**
	 * if the queries result should replace the identity map's content
	 *
	 * @var boolean
	 */
	protected $reload = FALSE;


	/**
	 * SQL DISTINCT
	 *
	 * @var boolean
	 */
	protected $unique = FALSE;


	/**
	 * Create a conditions to match a given Record or collection
	 *
	 * TODO: This method needs to be tested
	 *
	 * @param CollectionInterface, RecordInterface $source The Collection or Record to match
	 * @param array $sourceKeys The keys to identity the record
	 * @param array $targetKeys The key of the other side of the relation
	 *
	 * @return ConditionInterface
	 */
	public static function targetConditions($source, $sourceKeys, $targetKeys)
	{
		# On how many primary keys the relation is build of
		$targetKeySize = count($targetKeys);

		# The primary key's values of the source
		$sourceValues = array();

		# Is a source record given?
		if(empty($source))
		{
			for($i=0,$j=$targetKeySize;$i<$j;$i++)
			{
				$sourceValues[] = NULL;
			}
		}
		else
		{
			if( ! is_array($source) && ! $source instanceof Traversable)
			{
				$sources = array($source);
			}
			else
			{
				$sources = $source;
			}

			foreach($sources AS $source)
			{
				reset($targetKeys);
				$vals = array();
				foreach($sourceKeys AS $sourceKey)
				{

					if( ! $sourceKey->isLoadedFor($source))
					{
						continue 2;
					}

					$val = $sourceKey->getValueFor($source);

					if( ! current($targetKeys)->isValidValue($val))
					{
						continue 2;
					}
					next($targetKeys);
					$vals[] = $val;
				}

				$sourceValues[] = $vals;
			}

			$sourceValues = array_unique($sourceValues, SORT_REGULAR);

			if($targetKeySize===1)
			{
				$targetKey = reset($targetKeys);
				$sourceValues = ArrayTools::flatten($sourceValues);
				if(count($sourceValues) === 1)
				{
					$condition = new EqualToComparison($targetKey, $sourceValues[0]);
				}
				else
				{
					$condition = new InEnumComparison($targetKey, $sourceValues);
				}

			}
			else
			{
				$condition = new OrOperation();

				foreach($sourceValues AS $sourceValue)
				{
					$and = new AndOperation();

					reset($targetKeys);
					foreach($sourceValue AS $value)
					{
						$and->addOperand(new EqualToComparison(current($targetKeys),$value));
						next($targetKeys);
					}

					$condition->merge(array($and));
				}
			}

			return $condition;
		}
	}


	/**
	 * creates a new query for the given datasource and model while passing initial options
	 *
	 * @param string $dataSource the datasource the query points to
	 * @param string $model the model to select the data from
	 * @param string $options initial conditions,limit, offset...
	 */
	public function __construct($dataSource, $model, $options = array())
	{
		$this->dataSourceName = $dataSource->getName();
		$this->model = $model;

		$this->clearConditions();

		$this->merge($options);

		//echo '<span style="color:red" class="debug_output">['.$this->model.']</span>';
		static::$instances++;
	}

	public function __destruct()
	{
		//echo '<span style="color:blue" class="debug_output">[GC]</span>';
		static::$gc++;
	}

		public static $gc;
		public static $instances;

	public function __clone()
	{
		//echo '<span style="color:red" class="debug_output">[clone:'.$this->model.']</span>';
		static::$instances++;
	}

	public function getDataSource()
	{
		return DataSource::getByName($this->dataSourceName);
	}


	public function getModel()
	{
		return $this->model;
	}

	public function setFields($fields)
	{
		$newFields = $this->typeCastFields($fields);

		$this->fields = $newFields;
	}


	public function addFields($fields)
	{
		$castedFields = $this->typeCastFields($fields);
		$this->getFields();

		foreach($castedFields AS $key => $field)
		{
			if( ! in_array($field, $this->fields, TRUE))
			{
				$this->fields[$key] = $field;
			}
		}

		return $this;
	}


	protected function typeCastFields($fields)
	{
		$model = $this->model;
		$properties = $model::getProperties();

		$newFields = array();
		foreach($fields AS $field)
		{
			if(in_array($field, $properties, TRUE))
			{
				$newFields[$field->getName()] = $field;
			}
			elseif($field instanceof PropertyInterface && array_key_exists($field->getName(), $properties))
			{
				$newFields[$field->getName()] = $field;
			}
			elseif(is_scalar($field) && array_key_exists($field, $properties))
			{
				$newFields[$field] = $properties[$field];
			}
			elseif($field instanceof AggregatorInterface && array_key_exists($field->getProperty()->getName(), $properties))
			{
				$newFields[] = $field;
			}
			else
			{
				if(is_scalar($field))
				{
					$fieldName = $field;
				}
				elseif($field instanceof PropertyInterface)
				{
					$fieldName = $field->getName();
				}
				else
				{
					$fieldName = spl_object_hash($field);
				}

				throw new UndefinedPropertyException('Unknown field "' . $fieldName . '" for model "' . $model . '" (' . $model::getEntityName() . ')');
			}
		}

		return $newFields;
	}

	public function getFields()
	{
		if($this->fields === NULL)
		{
			$model = $this->getModel();
			$this->fields = $model::getDefaultProperties();
		}
		return $this->fields;
	}

	public function setLinks($links)
	{

		$this->links = $this->typeCastLinks($links);

		return $this;
	}

	public function getLinks()
	{
		return $this->links;
	}


	public function addLinks($links)
	{
		$links = $this->typeCastLinks($links);

		foreach($links AS $link)
		{
			if( ! in_array($links, $this->links, TRUE))
			{
				$this->links[] = $link;
			}
		}

		return $this;
	}


	protected function typeCastLinks($links)
	{
		foreach($links AS $link)
		{
			if( ! $link instanceof RelationshipInterface)
			{
				throw new InvalidRelationshipException('Link have to be a relationship');
			}
		}

		return $links;
	}


	public function setConditions($conditions)
	{
		$this->conditions = $conditions;

		$this->setLinks($this->extractLinks($conditions));

		return $this;
	}

	public function addConditions($conditions)
	{
		$this->conditions = $this->conditions->and_($conditions);

		$this->addLinks($this->extractLinks($conditions));

		return $this;
	}

	protected function extractLinks($conditions)
	{
		$links = array();

		if($conditions instanceof OperationInterface)
		{
			foreach($conditions AS $condition)
			{
				$extractedLinks = $this->extractLinks($condition);
				$extractedLinks = array_reverse($extractedLinks);
				foreach($extractedLinks AS $link)
				{
					if( ! in_array($link, $links, TRUE))
					{
						array_unshift($links, $link);
					}
				}
			}
		}
		elseif($conditions instanceof ComparisonInterface)
		{
			$subject = $conditions->getSubject();
			if($subject instanceof PathInterface)
			{
				$relationships = $subject->getRelationships();
				foreach($relationships AS $link)
				{
					if( ! in_array($link, $links, TRUE))
					{
						array_unshift($links, $link);
					}
				}
			}
		}

		return $links;
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	public function setOffset($offset)
	{
		if( ! is_int($offset))
		{
			throw new InvalidOffsetException("Limit has to be an integer but was '{$offset}'");
		}
		$this->offset = $offset;

		return $this;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function setLimit($limit)
	{
		if( ! is_int($limit))
		{
			throw new InvalidLimitException("Limit has to be an integer but was '{$limit}'");
		}
		$this->limit = $limit;

		return $this;
	}

	public function getLimit()
	{
		return $this->limit;

		return $this;
	}


	public function setOrder($orders)
	{
		$givenOrders = is_array($orders) ? $orders : array($orders);

		$orders = $this->typeCastOrder($givenOrders);

		$this->order = $orders;
	}

	protected function typeCastOrder($orders)
	{
		$result = array();

		foreach($orders AS $order)
		{
			if($order instanceof OrderInterface)
			{
				$result[] = $order;
			}
			elseif($order instanceof PropertyInterface)
			{
				$result[] = $order->asc();
			}
			else
			{
				throw new InvalidOrderException("Order have to be an OrderInterface object");
			}
		}

		return $result;
	}

	public function getOrder()
	{
		return $this->order;
	}


	public function addOrder($orders)
	{
		$orders = $this->typeCastOrder($orders);

		foreach($orders AS $order)
		{
			if( ! in_array($order, $this->order, TRUE))
			{
				$this->order[] = $order;
			}
		}

		return $this;
	}


	public function setReload($reload)
	{
		$this->reload = $reload;

		return $this;
	}

	public function toBeReloaded()
	{
		return $this->reload;
	}

	public function setUniqueness($unique)
	{
		$this->unique = (bool)$unique;

		return $this;
	}

	public function toBeUnique()
	{
		return $this->unique;
	}


	public function isValid()
	{

	}


	public function update($options)
	{
		if(isset($options['conditions']))
		{
			$this->setConditions($options['conditions']);
		}

		if(isset($options['order']))
		{
			$this->setOrder($options['order']);
		}

		if(isset($options['fields']))
		{
			$this->setFields($options['fields']);
		}

		if(isset($options['offset']))
		{
			$this->setOffset($options['offset']);
		}

		if(isset($options['limit']))
		{
			$this->setLimit($options['limit']);
		}
		if(isset($options['links']))
		{
			$this->setLinks($options['links']);
		}
		if(isset($options['reload']))
		{
			$this->setReload($options['reload']);
		}
		if(isset($options['unique']))
		{
			$this->setUniqueness($options['unique']);
		}

		return $this;
	}


	public function merge($options)
	{
		if(isset($options['conditions']))
		{
			$this->addConditions($options['conditions']);
		}

		if(isset($options['order']))
		{
			$this->addOrder($options['order']);
		}

		if(isset($options['fields']))
		{
			$this->setFields($options['fields']);
		}

		if(isset($options['offset']))
		{
			$this->setOffset($options['offset']);
		}

		if(isset($options['limit']))
		{
			$this->setLimit($options['limit']);
		}
		if(isset($options['links']))
		{
			$this->addLinks($options['links']);
		}
		if(isset($options['reload']))
		{
			$this->setReload($options['reload']);
		}
		if(isset($options['unique']))
		{
			$this->setUniqueness($options['unique']);
		}

		return $this;
	}


	public function clearConditions()
	{
		$this->conditions = new NullOperation();

		return $this;
	}


	public function filterRecords($givenRecords)
	{
		$resultRecords = array();

		foreach($givenRecords AS $record)
		{
			if($this->conditions->match($record))
			{
				$resultRecords[] = $record;
			}
		}

		$resultRecords = $this->orderRecords($resultRecords);

		// $resultRecords = $this->sliceRecords($resultRecords);

		return $resultRecords;
	}


	protected function orderRecords($records)
	{
		usort($records, array($this, 'orderCallback'));

		return $records;
	}


	protected function orderCallback($recordOne, $recordTwo)
	{
		$result = 0;

		foreach($this->order AS $order)
		{
			$result = $order->compare($recordOne, $recordTwo);
			if($result !== 0)
			{
				break;
			}
		}

		return $result;
	}


	protected function sliceRecords($records)
	{
		$offset = $this->getOffset();
		$limit = $this->getLimit();

		return array_slice($records ,$this->getOffset(), $limit);
	}


	protected function getOption($name)
	{
		$map = array(
			'conditions' => 'getConditions',
			'limit' => 'getLimit',
			'offset' => 'getOffset',
			'order' => 'getOrder',
			'fields' => 'getFields',
			'links' => 'getLinks',
			'unique' => 'toBeUnique',
			'reload' => 'toBeReloaded'
		);

		if(isset($map[$name]))
		{
			$method = $map[$name];
			return $this->$method();
		}

		throw new InvalidArgumentException("Unknown option '{$name}'");
	}


	public function offsetSet($offset, $value)
	{
		$this->getOption($offset);
		$this->update(array($offset => $value));
	}


	public function offsetGet($offset)
	{
		return $this->getOption($offset);
	}


	public function offsetExists($offset)
	{
		$map = array(
			'conditions' => 'getConditions',
			'limit' => 'getLimit',
			'offset' => 'getOffset',
			'order' => 'getOrder',
			'fields' => 'getFields',
			'links' => 'getLinks',
			'unique' => 'toBeUnique',
			'reload' => 'toBeReloaded'
		);

		return isset($map[$offset]) && $this->getOption($offset) !== NULL;
	}


	public function offsetUnset($offset)
	{
		throw new BadMethodCallException('You can not unset a query option.');
	}

	public function isSubsetOf($otherQuery)
	{
		if($otherQuery->toBeReloaded())
		{
			return FALSE;
		}
		if( ! $otherQuery->toBeUnique() && $this->toBeUnique())
		{
			return FALSE;
		}
		if( ! $this->rangeIsInsideOf($otherQuery))
		{
			return FALSE;
		}
		if( ! $this->isSortedTheSameAs($otherQuery) && $otherQuery->isSliced())
		{
			return FALSE;
		}

		return TRUE;
	}

	protected function rangeIsInsideOf($otherQuery)
	{
		if($this->limit > $otherQuery->getLimit())
		{
			return FALSE;
		}
		if($this->offset < $otherQuery->getOffset())
		{
			return FALSE;
		}
		if($this->limit - $this->offset > $otherQuery->getLimit() - $otherQuery->getOffset())
		{
			return FALSE;
		}

		return TRUE;
	}

	protected function isSortedTheSameAs($otherQuery)
	{
		$orderOne = $this->getOrder();
		$orderTwo = $otherQuery->getOrder();

		if(($length = count($orderOne)) !== count($orderTwo))
		{
			return FALSE;
		}

		for($i=0;$i<$length;$i++)
		{
			if( ! $orderOne[$i]->isEqualTo($orderTwo[$i]))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	public function isSliced()
	{
		return $this->offset!==0 || $this->limit!==0;
	}

}
