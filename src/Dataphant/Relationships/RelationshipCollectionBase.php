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

use Dataphant\Collection;
use Dataphant\Relationships\RelationshipCollectionInterface;

/*
 * RelationshipCollectionBase
 */
class RelationshipCollectionBase extends Collection implements RelationshipCollectionInterface
{

	/**
	 * The relationship the collection belongs to.
	 *
	 * @var RelationshipInterface
	 */
	protected $relationship;

	protected $source;

	public function setRelationship($relationship)
	{
		$this->relationship = $relationship;
	}

	public function getRelationship()
	{
		return $this->relationship;
	}

	public function setSource($source)
	{
		$this->source = $source;
	}

	public function getSource()
	{
		return $this->source;
	}

	protected function createNewCollection($query, $records = array())
	{
		$collection = parent::createNewCollection($query, $records);
		$collection->setRelationship($this->getRelationship());
		$collection->setSource($this->getSource());

		return $collection;
	}

	protected function loadRecords()
	{

		if( $this->isLoaded())
		{
			return;
		}

		$source = $this->getSource();
		$relationship = $this->getRelationship();

		if( ! $relationship->getValueFor($source)->isLoaded())
		{
			if( ! $source->isNew() && ($collection = $source->getCollection()))
			{
				$relationship->eagerLoadFor($collection, $this->query);
			}
		}

		if( ! $this->isLoaded())
		{
			$query = $this->getQuery();
			$records = $query->filterRecords($this->getRelationship()->getValueFor($this->getSource()));

			$this->setRecords($records);
		}

	}


	protected function getNewQuery($otherQuery = NULL)
	{

		if($query = $this->getQueryClone())
		{
			return $query->merge($otherQuery);
		}
		else
		{
			return $this->getRelationship()->getNewQueryFor($this->getSource())->merge($otherQuery);
		}
	}

	protected function isVirgin()
	{
		return FALSE;
	}

}
