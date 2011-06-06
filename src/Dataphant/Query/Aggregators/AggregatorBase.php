<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Aggregators;

use ReflectionProperty;

use Dataphant\Relationships\RelationshipCollectionInterface;
use Dataphant\Query\Query;

/*
 * AggregatorBase
 */
abstract class AggregatorBase implements AggregatorInterface
{

	protected $property;

	public function __construct($property)
	{
		$this->property = $property;
	}

	public function getProperty()
	{
		return $this->property;
	}

	public function getValueFor($collection)
	{
		$this->lazyLoadFor($collection);

		return $this->getValueForInternal($collection);
	}

	public function lazyLoadFor($collection)
	{
		if($this->isLoadedFor($collection))
		{
			return;
		}
		elseif($collection->isLoaded())
		{
			$this->setValueForInternal($collection, $this->calculateFor($collection));
			return;
		}

		$this->eagerLoadFor($collection);
	}

	public function eagerLoadFor($collection)
	{
		$model = $collection->getModel();
		$aggregates = $model::getDataSource()->aggregate($this->getNewQueryFor($collection));

		$this->associateTargets($collection, $aggregates);
	}

	protected function getNewQueryFor($collection)
	{
		$model = $collection->getModel();
		$query = $model::getDataSource()->getNewQuery($model);

		$query->setFields(array($this));

		if($this->canBeEagerLoadedFor($collection))
		{
			# TODO: get the base query of the relationship in this case
			$query->addFields($collection->getRelationship()->getTargetKeys());
		}

		$query->addConditions($this->getScope($collection));

		$query->setUniqueness(TRUE);
		return $query;
	}

	protected function getScope($collection)
	{
		if($this->canBeEagerLoadedFor($collection))
		{
			$sourceKeys = $collection->getRelationship()->getSourceKeys();
			$targetKeys = $collection->getRelationship()->getTargetKeys();

			# FIXME: This does not work for ManyToMany-Collection because the join model is not used
			# to build the condition.

			$conditions = Query::targetConditions($collection->getSource()->getCollection(), $sourceKeys, $targetKeys);
		}
		else
		{
			$conditions = $collection->getQuery()->getConditions();
		}
		return $conditions;
	}

	protected function associateTargets($collection, $aggregates)
	{
		if($this->canBeEagerLoadedFor($collection))
		{
			$aggregateMap = array();

			$relationship = $collection->getRelationship();
			$sourceKeys = $relationship->getSourceKeys();
			$targetKeys = $relationship->getTargetKeys();

			foreach($aggregates AS $aggregate)
			{
				# TODO: dry this, look @ModelBase::map()
				$keyMap = array();
				reset($targetKeys);
				foreach($sourceKeys AS $key)
				{
					$targetKey = current($targetKeys);
					if( isset($aggregate[ $targetKey->getFieldName() ]) && $key->isValidValue( $aggregate[ $targetKey->getFieldName() ] ))
					{
						$keyMap[] = $key->getFieldName() . ':' . $aggregate[ $targetKey->getFieldName() ];
						next($targetKeys);
					}
					else
					{
						continue 2;
					}
				}
				$key = join(';', $keyMap);

				$aggregateMap[$key] = $aggregate;

			}


			$sources = $collection->getSource()->getCollection();

			foreach($sources AS $source)
			{
				$key = $source->getKey();
				if( isset($aggregateMap[$key]))
				{
					$this->eagerLoadAggregate($relationship->getValueFor($source), $aggregateMap[$key]);
				}
				else
				{
					$this->eagerLoadAggregate($relationship->getValueFor($source), array());
				}
			}
		}
		else
		{
			$this->eagerLoadAggregate($collection, $aggregates[0]);
		}
	}

	protected function eagerLoadAggregate($collection, $aggregate)
	{
		if(isset($aggregate[$this->getAliasName()]))
		{
			$this->setValueForInternal($collection, $this->typecast($aggregate[$this->getAliasName()]));
		}
		else
		{
			$this->setValueForInternal($collection, 0);
		}
	}


	protected function canBeEagerLoadedFor($collection)
	{
		if	(
			$collection instanceof RelationshipCollectionInterface
			&&
			$collection === $collection->getRelationship()->getValueFor($collection->getSource())
			)
		{
			return TRUE;
		}

		return FALSE;
	}


	protected function setValueForInternal($collection, $value)
	{
		$reflection = $this->getReflection($collection);
		$aggregations = $reflection->getValue($collection);

		$slug = get_class($this);

		if( ! isset($aggregations[$slug]))
		{
			$aggregations[$slug] = array();
		}

		$aggregations[$slug][$this->getProperty()->getName()] = $value;

		$reflection->setValue($collection, $aggregations);

		return $value;
	}

	protected function getValueForInternal($collection)
	{
		$reflection = $this->getReflection($collection);
		$aggregations = $reflection->getValue($collection);

		$slug = get_class($this);

		if(isset($aggregations[$slug])
		&& array_key_exists($this->getProperty()->getName(), $aggregations[$slug]))
		{
			$value = $aggregations[$slug][$this->getProperty()->getName()];
		}
		else
		{
			return NULL;
		}


		return $value;
	}

	public function isLoadedFor($collection)
	{
		$reflection = $this->getReflection($collection);
		$aggregations = $reflection->getValue($collection);

		$slug = get_class($this);

		$isSet = isset($aggregations[$slug])
		      && array_key_exists($this->getProperty()->getName(), $aggregations[$slug]);

		return $isSet;
	}

	protected function getReflection($collection)
	{
		$reflection = new ReflectionProperty($collection, 'aggregations');
		$reflection->setAccessible(TRUE);

		return $reflection;
	}
}
