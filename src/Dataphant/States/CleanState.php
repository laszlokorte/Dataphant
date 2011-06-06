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
 * This state marks a record as having no changes compared to the database
 */
class CleanState extends PersistedStateBase
{
	public function set($property, $value)
	{
		if($this->isNotModified($property, $value))
		{
			return $this;
		}
		else
		{
			$state = new DirtyState($this->record);
			$this->record->setState($state);

			return $state->set($property, $value);
		}
	}

	public function delete()
	{
		return new DeletedState($this->record);
	}

	protected function isNotModified($property, $value)
	{
		return $property->isLoadedFor($this->record) && $property->getValueFor($this->record) === $value;
	}
}
