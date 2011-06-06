<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Relationships;

use ReflectionProperty;
use Traversable;

use Dataphant\Query\Query;
use Dataphant\RecordInterface;
use Dataphant\CollectionInterface;

use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\GreaterThanComparison;
use Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LessThanComparison;
use Dataphant\Query\Comparisons\LessThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LikeComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Utils\ArrayTools;

/**
 * This is the parent class of all other Relationship classes
 * it provides basic functionality as getting the source and target model's name
 */
abstract class RelationshipBase implements RelationshipInterface
{
	/**
	 * The relationships default options
	 */
	protected static $defaultOptions = array(

	);

	/**
	 * The relationships name from the perspective of the source model.
	 *
	 * @var string
	 */
	protected $name;


	/**
	 * The source models class name
	 * Eg \Models\User
	 *
	 * @var string
	 */
	protected $sourceModel;


	/**
	 * The target models class name
	 * Eg \Models\Comment
	 *
	 * @var string
	 */
	protected $targetModel;


	/**
	 * The opposite of the relationship.
	 *
	 * @var RelationshipInterface
	 */
	protected $inverse;


	/**
	 * The additional options set for a relationship
	 *
	 * @var string
	 */
	protected $options = array();


	/**
	 * Builds a new relationship but still have to be added to the sourceModel
	 *
	 * @param string $name the relationships name from the perspective of the source model
	 * @param string $sourceModel the source models class name including the namespace
	 * @param string $targetModel the target models class name including the namespace
	 * @param string $options the options to customize the relationship
	 */
	public function __construct($name, $sourceModel, $targetModel, $options = array())
	{
		$this->name = $name;
		$this->sourceModel = $sourceModel;
		$this->targetModel = $targetModel;

		$this->options = array_merge(static::$defaultOptions, $options);
	}


	public function getName()
	{
		return $this->name;
	}


	public function getSourceModel()
	{
		return $this->sourceModel;
	}


	public function getSourceKeys()
	{
		if( ! isset($this->sourceKeys))
		{
			$model = $this->getSourceModel();
			$this->sourceKeys = $model::getKeys();
		}
		return $this->sourceKeys;
	}

	public function getTargetModel()
	{
		return $this->targetModel;
	}


	public function getTargetKeys()
	{
		if( ! isset($this->targetKeys))
		{
			$targetModel = $this->getTargetModel();
			$this->targetKeys = $targetModel::getKeys();
		}

		return $this->targetKeys;
	}


	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * Get the relationship's value for the given $record.
	 *
	 * The reflection api is used to get the relationship's
	 * value directly out of the records protected property.
	 *
	 * @param RecordInterface $record
	 *
	 * @return mixed The relationships value.
	 */
	protected function getValueForInternal($record)
	{
		$reflection = $this->getAttributeReflection($record);

		$value = $reflection->getValue($record);

		if(isset($value[$this->getName()]))
		{
			return $value[$this->getName()];
		}
	}

	/**
	 * Get the relationship's $value for the given $record.
	 *
	 * The reflection api is used to write the relationship's
	 * value directly into the records protected property.
	 *
	 * @param RecordInterface $record
	 *
	 * @return mixed The relationships value.
	 */
	protected function setValueForInternal($record, $value)
	{
		$reflection = $this->getAttributeReflection($record);

		$newValue = $reflection->getValue($record);

		$newValue[$this->getName()] = $value;

		$reflection->setValue($record, $newValue);
	}


	/**
	 * Check if the relationship's $value is loaded for the given $record.
	 *
	 * The reflection api is used to read the relationship's
	 * value directly from the records protected property.
	 *
	 * @param RecordInterface $record
	 *
	 * @return mixed The relationships value.
	 */
	public function isLoadedFor($record)
	{
		$reflection = $this->getAttributeReflection($record);

		$attributes = $reflection->getValue($record);

		$isSet = array_key_exists($this->getName(), $attributes);

		return $isSet;
	}


	/**
	 * Removes the internal stored value to reload it on the next access.
	 *
	 * @param RecordInterface $record
	 *
	 * @return void
	 */
	protected function unloadForInternal($record)
	{
		$reflection = $this->getAttributeReflection($record);

		$newValue = $reflection->getValue($record);

		unset($newValue[$this->getName()]);

		$reflection->setValue($record, $newValue);
	}


	public function eagerLoadFor($collection, $otherQuery = NULL)
	{
		$model = $this->getTargetModel();

		$query = $this->getNewQueryFor($collection, $otherQuery);

		$targets = $model::find()->all($query);

		$this->associateTargets($collection, $targets);

		return $targets;
	}


	public function getNewQueryFor($source, $otherQuery = NULL)
	{

		$targetModel = $this->getTargetModel();

		$query = $targetModel::getDataSource()->getNewQuery($targetModel);
		$query['conditions'] = $this->getSourceScope($source);


		# TODO:
		# - test this
		# - clean this up
		# - the fields of the two queries get merged to allow eager load properties
		#   when an collection get eager-loaded

		$query->addFields($this->getTargetKeys());

		if($otherQuery)
		{
			$query->addFields($otherQuery->getFields());
		}


		return $query;
	}


	protected function getSourceScope($source)
	{
		# TODO:
		/*
			Create an EqualToComparison between the inverse relationship and the source.
			The Query class should only be accessed by the Adapter.

			$q = new \Dataphant\Query\Comparisons\EqualToComparison($this->getInverse(), $source);
		*/
		$q = Query::targetConditions($source, $this->getSourceKeys(), $this->getTargetKeys());

		return $q;
	}


	/**
	 * Associate the given targets with the given source record(s)
	 * This source could be a User record or a collection of Users and the
	 * targets could be a list of comments.
	 *
	 * This method makes all the users know their comments.
	 *
	 * @param RecordInterface, CollectionInterface $source A single record or a collection
	 * @param array $targets An array of records
	 *
	 * @return void
	 */
	protected function associateTargets($source, $targets)
	{

		$targetMaps = array();

		foreach($targets AS $target)
		{
			$keys = array();

			foreach($this->getTargetKeys() AS $targetKey)
			{
				$keys[] = $targetKey->getValueFor($target);
			}

			$key = join(';', $keys);

			if( ! isset($targetMaps[$key]))
			{
				$targetMaps[$key] = array();
			}
			$targetMaps[$key][] = $target;
		}

		if( ! is_array($source) && ! $source instanceof Traversable)
		{
			$sources = array($source);
		}
		else
		{

			$source->setChildCollectionFor($this, $targets);

			$sources = $source;
		}

		foreach($sources AS $source)
		{
			$keys = array();
			foreach($this->getSourceKeys() AS $sourceKey)
			{
				$keys[] = $sourceKey->getValueFor($source);
			}
			$key = join(';', $keys);

			if(isset($targetMaps[$key]))
			{
				$this->eagerLoadTargets($source, $targetMaps[$key]);
			}
			else
			{
				$this->eagerLoadTargets($source, array());
			}
		}
	}

	/**
	 * Get the reflection object for the given records $attributes property.
	 *
	 * @param RecordInterface $record
	 *
	 * @return ReflectionProperty
	 */
	protected function getAttributeReflection($record)
	{
		# getModel or getSourceModel?
		# TODO: Test if the reflection api refers to the complete class hirarchie or just to the
		# exact class being used with
		if( ! isset($this->reflection))
		{
			$this->reflection = new ReflectionProperty($this->getSourceModel(), 'attributes');
			$this->reflection->setAccessible(TRUE);
		}

		return $this->reflection;
	}


	public function isValidValue($value)
	{
		if($value instanceof RecordInterface)
		{
			return TRUE;
		}
		elseif($value instanceof CollectionInterface)
		{
			return TRUE;
		}
		elseif($value === NULL)
		{
			return TRUE;
		}

		return FALSE;
	}

	public function getInverse()
	{
		if( ! isset($this->inverse))
		{
			$targetModel = $this->getTargetModel();
			$relationships = $targetModel::getRelationships();

			foreach($relationships AS $possibleRelationship)
			{
				if($this->couldBeInverseOf($possibleRelationship) === TRUE)
				{
					$this->inverse = $possibleRelationship;
					break;
				}
			}
		}
		if( ! isset($this->inverse))
		{
			throw new \Exception("No matching inverse Relationship could be found for {$this->name}.");
		}

		return $this->inverse;
	}


	/**
	 * Check if the given Relationship could be the inverse of this one.
	 *
	 * @param Relationship $relationship
	 *
	 * @return boolean
	 */
	protected function couldBeInverseOf($relationship)
	{
		if($this->getTargetModel()!==$relationship->getSourceModel())
		{

			return FALSE;
		}
		if($this->getSourceModel()!==$relationship->getTargetModel())
		{
			return FALSE;
		}

		return TRUE;
	}

	public function setInverse($relationship)
	{
		$this->inverse = $relationship;
	}


	public function isMassAssignable()
	{
		return ! isset($this->options['mass_assignable']) || $this->options['mass_assignable'] !== FALSE;
	}


	public function isCrossDataSource()
	{
		$sourceModel = $this->getSourceModel();
		$sourceDataSource = $sourceModel::getDataSource();

		$targetModel = $this->getTargetModel();
		$targetDataSource = $targetModel::getDataSource();

		return ($sourceDataSource !== $targetDataSource);
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
