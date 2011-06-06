<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Utils;

/*
 * Inflection
 */
class Inflector implements InflectorInterface
{

	static protected $instance;

	static public function getInstance()
	{
		if(static::$instance === NULL)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	static public function clearInstance()
	{
		static::$instance = NULL;
	}

	private function __construct()
	{

	}

	/**
	 * Pluralize the given string.
	 * eg user -> users
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function pluralize($string)
	{
		#TODO: complex stuff

		if(substr($string, -1) !== 's')
		{
			return $string . 's';
		}
		return $string;
	}

	/**
	 * Singularize
	 * eg users -> user
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function singularize($string)
	{
		if(substr($string, -1, 1) === 's')
		{
			return substr($string, 0, -1);
		}
		return $string;
	}


	/**
	 * Camelize the given string.
	 * eg user_info -> UserInfo
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function camelize($string)
	{
		return str_replace(' ', '', $this->humanize($string));
	}


	/**
	 * Underscore the given string.
	 * eg UserName -> user_name
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function underscore($string)
	{
		return strtolower(preg_replace('/([^A-Z_])([A-Z])/', '\\1_\\2', $string));
	}


	/**
	 * Convert the given string into a readable one.
	 * eg user_name -> User Name
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function humanize($string)
	{
		return trim(ucwords(strtolower(str_replace('_', ' ', $this->underscore($string)))));
	}

}
