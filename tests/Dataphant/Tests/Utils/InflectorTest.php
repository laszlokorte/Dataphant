<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Tests\Utils;

use Dataphant\Tests\BaseTestCase;
use Dataphant\Utils\Inflector;

/*
 * InflectorTest
 */
class InflectorTest extends BaseTestCase
{

	public function setUp()
	{
		Inflector::clearInstance();
	}

	public function testPluralizing()
	{
		$i = Inflector::getInstance();

		$words = array(
			#TODO: more complex pluralization rules
			/*
			'person' => 'people',
			'child' => 'children'
			...
			*/
			'user' => 'users'
		);

		foreach($words AS $before => $after)
		{
			$this->assertSame($after, $i->pluralize($before), "{$before} gets pluralized to {$after}.");
		}

	}


	public function testSingularizing()
	{
		$i = Inflector::getInstance();

		$words = array(
			#TODO: more complex singularization rules
			/*
			'people' => 'person',
			'childdren' => 'child'
			...
			*/
			'users' => 'user'
		);

		foreach($words AS $before => $after)
		{
			$this->assertSame($after, $i->singularize($before), "{$before} gets singularized to {$after}.");
		}

	}


	public function testUnderscoring()
	{
		$i = Inflector::getInstance();

		$words = array(
			'UserInfo' => 'user_info',
			'Info' => 'info',
			'CSP' => 'csp',
			'CSP_ADDON' => 'csp_addon',
			'pascalCase' => 'pascal_case',
			'123' => '123',
			'___' => '___',
			'user_info' => 'user_info'
		);

		foreach($words AS $before => $after)
		{
			$this->assertSame($after, $i->underscore($before), "{$before} gets underscored to {$after}.");
		}

	}


	public function testCamelizing()
	{
		$i = Inflector::getInstance();

		$words = array(
			'user_info' => 'UserInfo',
			'info' => 'Info',
			'CSP_ADDON' => 'CspAddon',
			'pascalCase' => 'PascalCase',
			'123' => '123',
			'___' => '',
			'__FILE__' => 'File',
			'UserInfo' => 'UserInfo'
		);

		foreach($words AS $before => $after)
		{
			$this->assertSame($after, $i->camelize($before), "{$before} gets camelized to {$after}.");
		}

	}


	public function testHumanizing()
	{
		$i = Inflector::getInstance();

		$words = array(
			'user_info' => 'User Info',
			'info' => 'Info',
			'CSP_ADDON' => 'Csp Addon',
			'pascalCase' => 'Pascal Case',
			'123' => '123',
			'___' => '',
			'__FILE__' => 'File',
			'UserInfo' => 'User Info'
		);

		foreach($words AS $before => $after)
		{
			$this->assertSame($after, $i->humanize($before), "{$before} gets humanized to {$after}.");
		}

	}


}
