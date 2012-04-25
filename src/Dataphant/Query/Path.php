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

use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\NotEqualToComparison;
use Dataphant\Query\Comparisons\GreaterThanComparison;
use Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LessThanComparison;
use Dataphant\Query\Comparisons\LessThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LikeComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Query\Aggregators\AverageAggregator;
use Dataphant\Query\Aggregators\MaximumAggregator;
use Dataphant\Query\Aggregators\MinimumAggregator;
use Dataphant\Query\Aggregators\SumAggregator;
use Dataphant\Query\Aggregators\CountAggregator;

use Dataphant\Query\Exceptions\InvalidRelationshipException;
use Dataphant\Query\Exceptions\InvalidPropertyException;

use Dataphant\Relationships\ManyToManyRelationship;

use Dataphant\Utils\ArrayTools;

use BadMethodCallException;
use ArrayIterator;
use Dataphant\Exceptions\InvalidPathException;

class Path implements PathInterface
{

	/**
	 * The relationships the path contains
	 */
	protected $relationships = array();


	/**
	 * The last value of the relationships array
	 */
	protected $lastRelationship;

	/**
	 * The property the path points to
	 */
	protected $property;

	/**
	 * Builds a new path
	 *
	 * @param array $relationships a list of relationships the should contain
	 * @param Property $property the property the path should point to
	 */
	public function __construct($relationships, $property = NULL)
	{
		if(empty($relationships))
		{
			throw new InvalidRelationshipException('At least one relationship must be given');
		}

		foreach($relationships AS $relationship)
		{
			if($relationship->isCrossDataSource())
			{
				throw new InvalidPathException('Cross DB Joins are not allowed.');
			}

			if($relationship instanceof ManyToManyRelationship)
			{
				$links = $relationship->getLinks();
				foreach($links AS $link)
				{
					$this->relationships[] = $link;
				}
			}
			else
			{
				$this->relationships[] = $relationship;
			}
		}

		if($property !== NULL)
		{
			$lastRelationship = $this->getLastRelationship();
			$model = $lastRelationship->getTargetModel();
			$properties = $model::getProperties();

			if(! isset($properties[$property]))
			{
				throw new InvalidPropertyException("'{$model}' has no property named '{$property}'.");
			}

			$this->property = $properties[$property];
		}
	}

	public function getProperty()
	{
		return $this->property;
	}


	public function getRelationships()
	{
		return $this->relationships;
	}


	public function getLastRelationship()
	{
		if( ! isset($this->lastRelationship))
		{
			$this->lastRelationship = end($this->relationships);
		}
		return $this->lastRelationship;
	}


	/**
	 * catch all method calls and redirect them to
	 * either a last relationship's target model relationship
	 * or the property the path points to
	 * while derocating the object the call got redirected to with a new path object
	 *
	 * @param string $method
	 * @param string $arguments
	 * @return void
	 */
	public function __call($method, $arguments)
	{
		if(isset($this->property))
		{
			if(method_exists($this->property, $method))
			{
				return call_user_func_array(array($this->property, $method), $arguments);
			}
			else
			{
				$class = get_class($this->property);
				throw new BadMethodCallException("{$class}::{$method} does not excists");
			}
		}
		else
		{
			$targetModel = $this->getLastRelationship()->getTargetModel();
			$relationships = $targetModel::getRelationships();
			if(isset($relationships[$method]))
			{
				$rels = $this->relationships;
				$rels[] = $relationships[$method];
				return new static($rels);
			}
			else
			{
				$properties = $targetModel::getProperties();
				if(isset($properties[$method]))
				{
					return new static($this->relationships, $properties[$method]->getName());
				}
			}
		}

		$class = get_class();
		throw new BadMethodCallException("Called method {$class}::{$method}() does not exist.");
	}

	public function asc()
	{
		return $this->__call('asc', array());
	}

	public function desc()
	{
		return $this->__call('desc', array());
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

	public function notEq($value)
	{
		return new NotEqualToComparison($this, $value);
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

	public function getIterator()
	{
		return new ArrayIterator($this->relationships);
	}

}
