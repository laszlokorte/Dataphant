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

use Dataphant\Query\Exceptions\InvalidDirectionException;

use Dataphant\Exceptions\IncomparableException;

class Order implements OrderInterface
{
	static protected $validDirections = array(
		'asc',
		'desc'
	);

	/**
	 * The property to sort by
	 *
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * The direction (asc|desc)
	 *
	 * @var string
	 */
	protected $direction;



	/**
	 * Build a new order object
	 *
	 * @param string $property the property to sort by
	 * @param string $order the direction
	 */
	public function __construct($property, $direction)
	{
		$this->property = $property;
		if( ! in_array($direction, static::$validDirections, TRUE))
		{
			throw new InvalidDirectionException("Direction '{$direction}' is no valid direction to sort by.");
		}
		$this->direction = $direction;
	}

	public function getProperty()
	{
		return $this->property;
	}

	public function getDirection()
	{
		return $this->direction;
	}

	public function compareRecords($recordOne, $recordTwo)
	{
		$property = $this->getProperty();

		$reverse = $this->getDirection() === 'asc';
		$valueOne = $property->getValueFor($recordOne);
		$valueTwo = $property->getValueFor($recordTwo);

		# TODO: ? move this check (and may be the comparison itself into the property class??)
		if(is_numeric($valueOne)) # FIX "1e4" is accepted as numeric
		{
			$result = $this->compareNumerics($valueOne, $valueTwo);
		}
		elseif(is_string($valueOne))
		{
			$result = $this->compareNumerics($valueOne, $valueTwo);
		}
		elseif(is_bool($valueOne))
		{
			$result = $this->compareBooleans($valueOne, $valueTwo);
		}
		else
		{
			throw new IncomparableException('Values are not comparable');
		}

		if($reverse === TRUE)
		{
			$result *= -1;
		}

		return $result;
	}

	protected function compareStrings($stringOne, $stringTwo)
	{
		return strcasecmp($stringOne, $stringTwo);
	}

	protected function compareNumerics($numberOne, $numberTwo)
	{
		if($numberOne == $numberTwo) {
	        return 0;
	    }
	    return ($numberOne < $numberTwo) ? -1 : 1;
	}

	protected function compareBooleans($boolOne, $boolTwo)
	{
		if($boolOne === $boolTwo)
		{
			return 0;
		}
		elseif($boolOne)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}


	public function isEqualTo($otherOrder)
	{
		if($this->getProperty() !== $ptherOrder->getProperty())
		{
			return FALSE;
		}
		if($this->getDirection() !== $otherOrder->getDirection())
		{
			return FALSE;
		}

		return TRUE;
	}
}
