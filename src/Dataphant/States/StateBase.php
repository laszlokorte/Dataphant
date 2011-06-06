<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\States;

use Dataphant\Relationships\ManyToOneRelationship;

/**
 * All other record's states are base on this class.
 */
abstract class StateBase implements StateInterface
{


	protected $record;


	protected $originalAttributes = array();


	public function __construct($record)
	{
		$this->record = $record;
	}


	public function get($property)
	{
		return $property->getValueFor($this->record);
	}


	public function set($property, $value)
	{
		return $property->setValueFor($this->record, $value);
	}


	public function getOriginalAttributes()
	{
		return $this->originalAttributes;
	}


	protected function addToIdentityMap()
	{
		$identityMap = $this->getIdentityMap();
		$identityMap[$this->record->getKey()] = $this->record;
	}


	protected function getIdentityMap()
	{
		return $this->getDataSource()->getIdentityMap($this->getModel());
	}


	protected function getModel()
	{
		return $this->record->getModel();
	}


	protected function getDataSource()
	{
		$model = $this->getModel();
		return $model::getDataSource();
	}


	protected function setChildKeys()
	{
		$model = $this->getModel();
		$relationships = $model::getRelationships();

		foreach($relationships AS $relationship)
		{
			$this->setChildKey($relationship);
		}
	}


	protected function setChildKey($relationship)
	{
		if($relationship instanceof ManyToOneRelationship && ! $relationship->isLoadedFor($this->record))
		{
			$this->set($relationship, $this->get($relationship));
		}
	}

}
