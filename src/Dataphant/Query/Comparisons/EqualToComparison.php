<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Comparisons;

class EqualToComparison extends ComparisonBase
{
	public function match($record)
	{
		return $this->getGivenValueFor($record) == $this->getNeededValueFor($record);
	}

	public function applyToRecord($record)
	{
		/*
			This is a fix for this case:

			Team(1:n)Memberships(n:1)User
			Team(n:m)User (through Memberships)

			An user gets added to the user-collection of a team.
			The user-collection's conditions are, that the team_id of the membership
			has to be equal to the team's id

			In this case the condition is based an membership object and can not be applied
			to the user object added to the team's collection.

			TODO/FIX: change the collection's condition to be based on the many-to-many relationship
			but not on the intermediary-relationship.

		*/
		if($this->isComparingRelationship() && $this->subject->getSourceModel() !== $record->getModel())
		{
			return;
		}
		$record->setAttribute($this->subject->getName(), $this->value);
	}
}
