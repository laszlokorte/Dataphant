<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Comparisons;

use Dataphant\Query\ConditionBase;
use Dataphant\Relationships\RelationshipInterface;
use Dataphant\ComparableInterface;

abstract class ComparisonBase extends ConditionBase implements ComparisonInterface
{
	/**
	 * the subject to compare with the value
	 *
	 * @var Subject|Path
	 */
	protected $subject;

	/**
	 * The value the subject gets compared with
	 *
	 * @var mixed
	 */
	protected $value;


	/**
	 * build a comparison between the subject and the value
	 * The subject can either be a property or a path
	 *
	 * @param Property|Path $subject
	 * @param string $value
	 */
	public function __construct($subject, $value)
	{
		$this->subject = $subject;
		$this->value = $value;

		parent::__construct();
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function isValid()
	{
		return TRUE;
	}

	public function applyToRecord($record)
	{
		# Do nothing by default
	}

	public function isComparingRelationship()
	{
		if($this->getSubject() instanceof RelationshipInterface)
		{
			return TRUE;
		}
		return FALSE;
	}

	protected function getGivenValueFor($record)
	{
		if($this->subject instanceof ComparableInterface)
		{
			return $this->subject->getValueFor($record);
		}

		return $this->subject;
	}

	protected function getNeededValueFor($record)
	{
		if($this->value instanceof ComparableInterface)
		{
			return $this->value->getValueFor($record);
		}

		return $this->value;
	}
}
