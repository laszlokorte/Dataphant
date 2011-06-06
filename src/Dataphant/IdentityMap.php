<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant;

class IdentityMap implements IdentityMapInterface
{
	/**
	 * Build a new identity map
	 */
	public function __construct()
	{

	}


	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}


	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}


	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}


	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

}
