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

/*
 * OneToManyCollection
 */
class OneToManyCollection extends RelationshipCollectionBase
{

	protected function markRecordAdded($record)
	{
		$record = parent::markRecordAdded($record);
		$this->inverseSet($record, $this->getSource());

		return $record;
	}

	protected function markRecordRemoved($record)
	{
		$record = parent::markRecordRemoved($record);
		$this->inverseSet($record, NULL);

		return $record;
	}

	protected function inverseSet($source, $target)
	{
		$this->getRelationship()->getInverse()->setValueFor($source, $target);
	}


}
