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

/**
 * This state marks a record as locked. Properties can not be changed and the record can not be deleted.
 * Not loaded Attributes can not be loaded afterwards.
 */
class ImmutableState extends PersistedStateBase
{
	public function get($property)
	{
		if( ! $property->isLoadedFor($this->record))
		{
			throw new ImmutableException('Immutable record can not be changed.');
		}

		return $property->getValueFor($this->record);
	}

	public function set($property, $value)
	{
		throw new ImmutableException('Immutable record can not be changed.');
	}

	public function commit()
	{
		return $this;
	}

	public function rollback()
	{
		return $this;
	}

	public function delete()
	{
		throw new ImmutableException('Immutable record can not be deleted.');
	}
}
