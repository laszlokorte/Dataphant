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

use Dataphant\States\Exceptions\DeletedImmutableException;

/**
 * Marks the record as deleted. It can not be modified anymore. The next commit will drop it from the database
 */
class DeletedState extends PersistedStateBase
{

	public function set($property, $value)
	{
		throw new DeletedImmutableException('Deleted record can not be changed.');
	}

	public function commit()
	{
		$this->deleteRecord();
		$this->removeFromIdentityMap();
		return new ImmutableState($this->record);
	}

	protected function deleteRecord()
	{
		$this->getDataSource()->delete($this->record->getCollectionForSelf());
	}

}
