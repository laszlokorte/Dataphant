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

/**
 * Records marked with this state have never been saved in the database. When commited the record have to be inserted. When rolled back nothing changes.
 */
class TransientState extends StateBase
{

	public function __construct($record)
	{
		parent::__construct($record);

		$model = $this->getModel();
		if($model::getDiscriminator())
		{
			$this->track($model::getDiscriminator());
		}
	}


	public function commit()
	{
		$this->setChildKeys();

		$this->setDefaultValues();

		if( ! $this->isValid())
		{
			return $this;
		}

		$this->createRecord();
		#set_repository
		$this->addToIdentityMap();
		return new CleanState($this->record);
	}

	public function delete()
	{
		return $this;
	}

	public function rollback()
	{
		return $this;
	}

	public function get($property)
	{
		return parent::get($property);
	}


	public function set($property, $value)
	{
		$this->track($property);
		parent::set($property, $value);
		return $this;
	}

	protected function track($property)
	{
		$propertyName = $property->getName();
		$this->originalAttributes[$propertyName] = NULL;
	}

	protected function createRecord()
	{
		$this->getDataSource()->create(array($this->record));
	}

	protected function isValid()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		foreach($properties AS $property)
		{
			if($property->isSerial() && $property->getValueFor($this->record) === NULL)
			{
				continue;
			}
			elseif( ! $property->isValidValue($property->getValueFor($this->record)))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	protected function setDefaultValues()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		foreach($properties AS $property)
		{
			$this->setDefaultValueOf($property);
		}
	}

	protected function setDefaultValueOf($property)
	{
		if( ! $property->isLoadedFor($this->record) && $property->hasDefaultValue())
		{
			$this->set($property, $property->getDefaultValueFor($this->record));
		}
	}

}
