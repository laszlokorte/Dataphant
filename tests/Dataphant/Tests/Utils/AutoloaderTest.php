<?php

namespace Dataphant\Tests\Utils;

use Dataphant\Tests\BaseTestCase;

require_once __DIR__ . '/../../../../src/Dataphant/Utils/Autoloader.php';

use Dataphant\Utils\Autoloader;

class AutoloaderTest extends BaseTestCase
{

	public function testInstanceCanBeRegistered()
	{
		$autoloader = new Autoloader();
		$autoloader->register();

		$this->assertTrue(in_array(array($autoloader, 'load'), spl_autoload_functions(), TRUE));
	}

	public function testHasNoRegisteredNamespacesOnInitialization()
	{
		$autoloader = new Autoloader();
		$propertyReflection = new \ReflectionProperty('Dataphant\Utils\Autoloader', 'namespaces');
		$propertyReflection->setAccessible(TRUE);

		$this->assertSame(array(), $propertyReflection->getValue($autoloader));
	}

	public function testNamespaceCanBeRegistered()
	{
		$autoloader = new Autoloader();
		$autoloader->registerNamespace('Dataphant', 'libs');
		$propertyReflection = new \ReflectionProperty('Dataphant\Utils\Autoloader', 'namespaces');
		$propertyReflection->setAccessible(TRUE);

		$this->assertSame(array('Dataphant' => 'libs'), $propertyReflection->getValue($autoloader));
	}

	public function testExistingClassInRegisteredNamespaceCanBeLoaded()
	{
		$autoloader = new Autoloader();
		$autoloader->registerNamespace('Dataphant\\Tests', __DIR__ . '/../../../');
		$autoloader->load('Dataphant\\Tests\\Mocks\\AutoloadedClass');

		$this->assertTrue(class_exists('Dataphant\\Tests\\Mocks\\AutoloadedClass', FALSE));
	}

	public function testNotExistingClassInRegisteredNamespaceCanNotBeLoaded()
	{
		$autoloader = new Autoloader();
		$autoloader->registerNamespace('Some\\Namespace', '../someDirectory');
		$autoloader->load('Some\\Namespace\\Not\\ExistingClass');

		$this->assertFalse(class_exists('Some\\Not\\ExistingClass', false));
	}

	public function testClassInNotRegisteredNamespaceCannotBeLoaded()
	{
		$autoloader = new Autoloader();
		$autoloader->registerNamespace('First\\NameSpace', '../firstDirectory');
		$autoloader->load('Second\\Namespace\\ExistingClass');

		$this->assertFalse(class_exists('Second\\Namespace\\ExistingClass', false));
	}

}
