<?php

/*
 * This file is part of ProjectName.
 *
 * (c) 2011 ProjectName Team
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Properties;

/*
 * DiscriminatorProperty
 */
class DiscriminatorProperty extends StringProperty
{
	static protected $defaultOptions = array(
		'length' => 250,
		'required' => FALSE
	);

	static protected $forcedOptions = array(
		'unique' => FALSE,
		'lazy' => FALSE,
		'writeable' => FALSE,
		'readable' => TRUE,
		'key' => FALSE,
		'default' => NULL,
	);

	public function isValidValue($value)
	{
		$model = $this->getModel();

		return parent::isValidValue($value) && is_subclass_of($value, $model);
	}

	public function getValueFor($record)
	{
		return get_class($record);
	}

	public function isLoadedFor($record)
	{
		return TRUE;
	}

}
