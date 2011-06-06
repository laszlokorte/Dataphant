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
 * Parent class of states marking a record as persisted
 */
abstract class PersistedStateBase extends StateBase
{

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
		return $this;
	}


	public function get($property)
	{
		return parent::get($property);
	}


	public function set($property, $value)
	{
		parent::set($property, $value);
		return $this;
	}

	protected function removeFromIdentityMap()
	{
		$identityMap = $this->getIdentityMap();
		unset($identityMap[$this->record->getKey()]);
	}

}
