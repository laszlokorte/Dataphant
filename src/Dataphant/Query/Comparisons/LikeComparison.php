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

class LikeComparison extends ComparisonBase
{

	protected $escapeCharacter = '\\';

	public function match($record)
	{
		return 0 < preg_match($this->getNeededValueFor($record), $this->getGivenValueFor($record));
	}


	protected function getNeededValueFor($record)
	{
		if($this->value instanceof ComparableInterface)
		{
			return $this->value->getValueFor($record);
		}

		return $this->convertToRegex($this->value);
	}


	protected function convertToRegex($like)
	{
		$placeholders = array(
			'%' => '.+',
			'_' => '.',
		);

		$pattern = $like;
		$escapeCharacter = str_replace('\\','\\\\', $this->escapeCharacter);


		foreach($placeholders AS $old => $new)
		{
			$pattern = preg_replace("/([^{$escapeCharacter}]){$old}/i", "$1{$new}", $pattern);
		}

		$pattern = "/{$pattern}/i";
		return $pattern;
	}
}
