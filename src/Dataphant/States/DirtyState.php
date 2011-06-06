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

use Dataphant\States\Exceptions\ImmutableException;
use ReflectionProperty;

/**
 * This state marks a record as "dirty". The object has changed since it was fetch from the database.
 * Changes will be lost when they does not get commited
 */
class DirtyState extends PersistedStateBase
{
	public function commit()
	{
		try
		{
			$this->removeFromIdentityMap();

			$this->setChildKeys();

			if( ! $this->isValid())
			{
				return $this;
			}

			$this->updateRecord();
			$this->resetOriginalAttributes();
			$this->resetRecordsKey();
			$this->addToIdentityMap();
			return new CleanState($this->record);
		}
		catch(\Exception $e)
		{
			$this->addToIdentityMap();
			throw $e;
		}
	}

	public function rollback()
	{
		$this->resetRecord();
		return new CleanState($this->record);
	}

	public function delete()
	{
		$this->resetRecord();
		return new DeletedState($this->record);
	}

	public function set($property, $value)
	{
		$this->track($property, $value);

		parent::set($property, $value);

		if( ! empty($this->originalAttributes))
		{

			return $this;
		}
		return new CleanState($this->record);
	}

	protected function track($property, $value)
	{
		$propertyName = $property->getName();

		// check if this property is dirty already
		if(array_key_exists($propertyName, $this->originalAttributes))
		{
			// if the new properties value is equal to the old, tracked value,
			// the tracked value is not needed anymore
			if($this->originalAttributes[$propertyName] === $value)
			{
				unset($this->originalAttributes[$propertyName]);
			}
		}
		elseif(($original = $property->getValueFor($this->record)) != $value)
		{

			$this->originalAttributes[$propertyName] = $original;
		}
	}


	protected function resetOriginalAttributes()
	{
		$this->originalAttributes = array();
	}

	protected function resetRecord()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();
		foreach($this->getOriginalAttributes() AS $attr => $value)
		{
			$properties[$attr]->setValueFor($this->record, $value);
		}
		$this->resetOriginalAttributes();
	}

	protected function resetRecordsKey()
	{
		$reflection = $this->getKeyPropertyReflection();
		$reflection->setValue($this->record, NULL);
	}

	protected function updateRecord()
	{
		$this->getDataSource()->update($this->record->getDirtyAttributes(), $this->record->getCollectionForSelf());
	}

	protected function isValid()
	{
		$model = $this->getModel();
		$properties = $model::getProperties();

		foreach($properties AS $property)
		{
			if( ! isset($this->originalAttributes[$property->getName()]))
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

	protected function getKeyPropertyReflection()
	{
		if( ! isset($this->reflection))
		{
			$this->reflection = new ReflectionProperty($this->getModel(), 'key');
			$this->reflection->setAccessible(TRUE);
		}

		return $this->reflection;
	}

}
