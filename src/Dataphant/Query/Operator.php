<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query;

class Operator implements OperatorInterface
{
	/**
	 * The operator slug (eg. 'count')
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The property the operator is applied to
	 *
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * Applie a operator on a property
	 *
	 * @param string $slug the operators slug
	 * @param string $property
	 */
	public function __construct($slug, $property)
	{
		$this->slug = $slug;

		$this->property = $property;
	}

	public function getSlug()
	{
		return $this->slug;
	}

	public function getProperty()
	{
		return $this->property;
	}
}
