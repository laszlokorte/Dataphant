<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Properties;

use Dataphant\Properties\PropertyInterface;

use Dataphant\Query\Query;
use Dataphant\Query\Order;
use Dataphant\Query\Aggregators\AverageAggregator;
use Dataphant\Query\Aggregators\MaximumAggregator;
use Dataphant\Query\Aggregators\MinimumAggregator;
use Dataphant\Query\Aggregators\SumAggregator;
use Dataphant\Query\Aggregators\CountAggregator;

use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\GreaterThanComparison;
use Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LessThanComparison;
use Dataphant\Query\Comparisons\LessThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LikeComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Utils\ArrayTools;

use ReflectionProperty;

abstract class PropertyBase implements PropertyInterface
{

	/**
	 * Options to be set for all properties by default.
	 *
	 * @var array
	 */
	static protected $defaultOptions = array(
		'unique' => FALSE,
		'lazy' => FALSE,
		'default' => NULL,
		'length' => 0
	);


	/**
	 * Options which value can not be changed for a specific property type.
	 *
	 * @var array
	 */
	static protected $forcedOptions = array(

	);

	/**
	 * The datasource the property belongs to
	 *
	 * @var DataSourceInterface
	 */
	protected $dataSource;

	/**
	 * The Model the property belongs to
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * The properties name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The properties options (length, precisison, ...)
	 *
	 * @var string
	 */
	protected $options = array();

	/**
	 * scalar identifier for the property
	 *
	 * @var string
	 */
	protected $hash;

	/**
	 * build a new property to be added to a model
	 *
	 * @param string $model
	 * @param string $name
	 * @param string $options
	 */
	public function __construct($model, $name, $options = array())
	{
		$this->model = $model;
		$this->name = $name;

		if(isset($options['accessible']) && $options['accessible']===FALSE)
		{
			$options['readable'] = FALSE;
			$options['writable'] = FALSE;
		}

		$this->options = array_merge(static::getDefaultOptions(), $options, static::getForcedOptions());
	}


	static protected function getDefaultOptions()
	{
		$baseProperty = static::getBaseProperty();

		if($baseProperty !== NULL)
		{
			$parentOptions = $baseProperty::getDefaultOptions();
		}
		else
		{
			$parentOptions = array();
		}

		return array_merge($parentOptions, static::$defaultOptions);
	}


	static protected function getForcedOptions()
	{
		$baseProperty = static::getBaseProperty();

		if($baseProperty !== NULL)
		{
			$parentOptions = $baseProperty::getForcedOptions();
		}
		else
		{
			$parentOptions = array();
		}

		return array_merge($parentOptions, static::$forcedOptions);
	}


	static public function getBaseProperty()
	{
		$base = get_parent_class(get_called_class());

		return $base ? $base : NULL;
	}


	/**
	 * Get a property object for the given data type.
	 *
	 * @param string $type The propertie's type
	 * @param string $model The model the property should belong to
	 * @param string $name The propertie's name
	 * @param string $options Additional options
	 *
	 * @return PropertyInterface
	 */
	static public function createPropertyOfType($type, $model, $name , $options = array())
	{
		$propertyClass = 'Dataphant\\Properties\\' . $type . 'Property';
		$property = new $propertyClass($model, $name, $options);

		return $property;
	}


	public function getDataSource()
	{
		if( ! isset($this->dataSource))
		{
			$model = $this->getModel();
			$this->dataSource = $model::getDataSource();
		}
		return $this->dataSource;
	}

	public function getModel()
	{
		return $this->model;
	}


	public function getName()
	{
		return $this->name;
	}

	public function getType()
	{
		# the class name without it's "Property" postfix
		$namespaceSplit = explode('\\', get_class($this));
		return substr(end($namespaceSplit),0,-8);
	}

	public function getFieldName()
	{
		if( ! isset($this->fieldName))
		{
			if(isset($this->options['fieldname']))
			{
				$this->fieldName = $this->options['fieldname'];
			}
			else
			{
				$this->fieldName = $this->getName();
			}
		}

		return $this->fieldName;
	}

	public function isUnique()
	{
		return $this->options['unique'] || $this->isSerial();
	}


	public function isRequired()
	{
		return isset($this->options['required']) && $this->options['required'];
	}


	public function isLazy()
	{
		return isset($this->options['lazy']) && $this->options['lazy'];
	}


	public function serialize($value)
	{
		return $value;
	}


	public function unserialize($value)
	{
		return $value;
	}


	public function isKey()
	{
		return isset($this->options['key']) && $this->options['key']===TRUE;
	}


	public function isSerial()
	{
		return isset($this->options['serial']) && (bool)$this->options['serial'];
	}


	public function getDefaultValueFor($record)
	{
		if($this->hasDefaultValue())
		{
			if(is_callable($this->options['default']))
			{
				call_user_func_array($this->options['default'], array($this, $record));
			}
			else
			{
				return $this->options['default'];
			}
		}
		else
		{
			return NULL;
		}
	}


	public function hasDefaultValue()
	{
		return isset($this->options['default']);
	}


	public function getLength()
	{
		return $this->options['length'];
	}


	public function getValueFor($record)
	{
		$this->lazyLoadFor($record);

		$reflection = $this->getReflection();

		$value = $reflection->getValue($record);
		if(isset($value[$this->getName()]))
		{
			$value = $value[$this->getName()];
		}
		else
		{
			$value = $this->getDefaultValueFor($record);
		}
		return $value;
	}


	public function setValueFor($record, $value)
	{
		$value = $this->typecast($value);

		$reflection = $this->getReflection();

		$newValue = $reflection->getValue($record);

		$newValue[$this->getName()] = $value;

		$reflection->setValue($record, $newValue);
	}


	protected function typecast($value)
	{
		return $value;
	}


	public function isLoadedFor($record)
	{
		$reflection = $this->getReflection();

		$attributes = $reflection->getValue($record);

		$isSet = array_key_exists($this->getName(), $attributes);

		return $isSet;
	}


	protected function getReflection()
	{
		if( ! isset($this->reflection))
		{
			$this->reflection = new ReflectionProperty($this->getModel(), 'attributes');
			$this->reflection->setAccessible(TRUE);
		}

		return $this->reflection;
	}


	public function isWriteable()
	{
		return ! isset($this->options['writeable']) || $this->options['writeable']!==FALSE;
	}


	public function isReadable()
	{
		return ! isset($this->options['readable']) || $this->options['readable']!==FALSE;
	}

	public function isMassAssignable()
	{
		return $this->isWriteable() && ( ! isset($this->options['mass_assignable']) || $this->options['mass_assignable'] !== FALSE );
	}

	public function lazyLoadFor($record)
	{
		if($this->isLoadedFor($record) || $record->isNew())
		{
			return;
		}

		$this->eagerLoadFor($record->getCollection());
	}

	public function eagerLoadFor($collection)
	{
		$model = $collection->getModel();
		$model::find()->all($this->getNewQueryFor($collection))->reload();
	}

	protected function getNewQueryFor($collection)
	{
		$model = $this->getModel();

		$query = $model::getDataSource()->getNewQuery($model);
		$query['fields'] = array_merge($model::getKeys(), array($this->getFieldName() => $this));
		$query->addConditions($this->getScope($collection));

		return $query;
	}

	protected function getScope($collection)
	{
		$model = $collection->getModel();
		$q = Query::targetConditions($collection, $model::getKeys(), $model::getKeys());

		return $q;
	}

	public function isValidValue($value)
	{
		$serializedValue = $this->serialize($value);

		if($this->isRequired() && $serializedValue === NULL)
		{
			return FALSE;
		}
		return TRUE;
	}

	public function asc()
	{
		return new Order($this, 'asc');
	}


	public function desc()
	{
		return new Order($this, 'desc');
	}


	public function avg()
	{
		return new AverageAggregator($this);
	}


	public function count()
	{
		return new CountAggregator($this);
	}


	public function min()
	{
		return new MinimumAggregator($this);
	}


	public function max()
	{
		return new MaximumAggregator($this);
	}


	public function sum()
	{
		return new SumAggregator($this);
	}


	public function eq($value)
	{
		return new EqualToComparison($this, $value);
	}


	public function gt($value)
	{
		return new GreaterThanComparison($this, $value);
	}


	public function gte($value)
	{
		return new GreaterThanOrEqualToComparison($this, $value);
	}


	public function lt($value)
	{
		return new LessThanComparison($this, $value);
	}


	public function lte($value)
	{
		return new LessThanOrEqualToComparison($this, $value);
	}


	public function like($value)
	{
		return new LikeComparison($this, $value);
	}


	public function in($value)
	{
		$value = ArrayTools::flatten(func_get_args());

		return new InEnumComparison($this, $value);
	}

}
