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

use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\GreaterThanComparison;
use Dataphant\Query\Comparisons\GreaterThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LessThanComparison;
use Dataphant\Query\Comparisons\LessThanOrEqualToComparison;
use Dataphant\Query\Comparisons\LikeComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

use Dataphant\Utils\ArrayTools;

class OneToOneRelationship implements RelationshipInterface
{

	protected $relationship;


	public function __construct($name, $sourceModel, $targetModel, $options = array())
	{
		$relationshipClass = __NAMESPACE__ . '\\OneToManyRelationship';

		$this->relationship = new $relationshipClass($name, $sourceModel, $targetModel, $options);
	}


	public function getName()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getSourceModel()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getSourceKeys()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getTargetModel()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getTargetKeys()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getOptions()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getValueFor($sourceRecord)
	{
		return $this->relationship->getValueFor($sourceRecord)->first();
	}


	public function setValueFor($sourceRecord, $target)
	{
		return $this->relationship->setValueFor($sourceRecord, array($target));
	}


	public function isLoadedFor($sourceRecord)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function lazyLoadFor($sourceRecord)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function eagerLoadFor($collection, $otherQuery = NULL)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function isValidValue($value)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getCollectionFor($source)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getNewQueryFor($source, $otherQuery = NULL)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function getInverse()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function setInverse($relationship)
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}


	public function isMassAssignable()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function isCrossDataSource()
	{
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->relationship, $method), $args);
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
