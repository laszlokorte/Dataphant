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

class Autoloader
{

	protected $namespaces = array();

	protected $namespaceSeparator = '\\';

	protected $extension = '.php';

	/**
	 * registers the autoloader instance
	 *
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register(array($this,'load'));
	}


	/**
	 * load include the file the given class should be defined in
	 *
	 * @param string $className
	 * @return void
	 */
	public function load($className)
	{
		if($path = $this->getPath($className))
		{
			require_once($path);
		}
	}


	/**
	 * register a specific path for a namespace
	 *
	 * @param string $namespace
	 * @param string $path
	 * @return void
	 */
	public function registerNamespace($namespace, $path)
	{
		$this->namespaces[trim($namespace,$this->namespaceSeparator)] = rtrim($path, DIRECTORY_SEPARATOR);
	}


	protected function getPath($className)
	{
		$trimmedClass = trim($className, $this->namespaceSeparator);

		foreach($this->namespaces AS $namespace => $path)
		{
			if(strpos($trimmedClass, $namespace)===0)
			{
				$p = $path . DIRECTORY_SEPARATOR . str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $className) . $this->extension;
				if(file_exists($p))
				{
					return $p;
				}
			}
		}
	}
}
