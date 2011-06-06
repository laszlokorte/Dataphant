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

use Dataphant\Relationships\RelationshipCollectionBase;
use Dataphant\Query\Comparisons\EqualToComparison;
use Dataphant\Query\Comparisons\InEnumComparison;

/*
 * ManyToManyCollection
 */
class ManyToManyCollection extends OneToManyCollection
{

	protected $intermediaries;

	protected $intermediaryMap;

	public function save()
	{
		$via = $this->getVia();
		$intermediaries = $this->getIntermediaries();

		if(count($this->removedRecords) > 0)
		{
			$intermediaries->filter(new InEnumComparison($via, $this->removedRecords))->destroy();

			$this->resetIntermediaries();
		}

		if($via instanceof ManyToOneRelationship)
		{
			parent::save();
			foreach($this->records AS $record)
			{
				if( ! $this->createIntermediaryFor($record))
				{
					return FALSE;
				}
			}
		}
		else
		{
			if(count($this->records) > 0 && $intermediary = $this->createIntermediaryFor())
			{
				$inverse = $via->getInverse();
				foreach($this->records AS $record)
				{
					$inverse->setValueFor($record, $intermediary);
				}
			}
			parent::save();
		}
	}

	protected function createIntermediaryFor($record)
	{
		$intermediaryMap = $this->getIntermediaryMap();
		$index = $record === NULL ? 0 : spl_object_hash($record);

		if(isset($intermediaryMap[$index]))
		{
			return $intermediaryMap[$index];
		}

		$intermediaries = $this->getIntermediaries();

		if( ! $intermediaries->save())
		{
			return FALSE;
		}


		$intermediaries = $intermediaries->filter(new EqualToComparison($this->getVia(), $record));
		if($intermediaries->first() === NULL)
		{
			$intermediaries[] = array();
			$this->getVia()->setValueFor($intermediaries->first(), $record);
			$this->intermediaries[] = $intermediaries->first();
		}

		if( ! $a = $intermediaries->save())
		{
			return FALSE;
		}

		$this->intermediaryMap[$index] = $intermediaries->first();

		return $this->intermediaryMap[$index];
	}

	protected function getIntermediaryMap()
	{
		return $this->intermediaryMap;
	}

	protected function getVia()
	{
		return $this->getRelationship()->getVia();
	}

	protected function getThrough()
	{
		return $this->getRelationship()->getThrough();
	}

	public function getIntermediaries()
	{
		if( ! isset($this->intermediaries))
		{
			$through = $this->getThrough();
			$source = $this->getSource();

			if( ! $through->isLoadedFor($source))
			{
				$this->resetIntermediaries();
			}

			$this->intermediaries = $through->getCollectionFor($source);

		}
		return $this->intermediaries;
	}

	/**
	 * Reset the collection of intermediaries.
	 * Set
	 *
	 * @return void
	 */
	protected function resetIntermediaries()
	{
		$through = $this->getThrough();
		$source = $this->getSource();

		$through->getValueFor($source)->reset();
	}

	protected function inverseSet($source, $target)
	{
		# what to do here?
	}


}
