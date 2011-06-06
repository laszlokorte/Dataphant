<?php

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;

use Dataphant\IdentityMap;

class IdentityMapTest extends BaseTestCase
{

	public function testDataCanBeSetAndReadViaArrayAccess()
	{
		$identityMap = new IdentityMap();

		$user = 'Jimmy';
		$id = 23;

		$this->identityMap[$id] = $user;
		$this->assertSame($user, $this->identityMap[$id]);
	}

	public function testDataCanBeUnsetViaArrayAccess()
	{
		$identityMap = new IdentityMap();

		$user = 'Jimmy';
		$id = 23;

		$identityMap[$id] = $user;
		$this->assertSame($user, $identityMap[$id]);

		unset($identityMap[$id]);

		$this->assertFalse(isset($identityMap[$id]));
	}

	public function testDataCanBeCheckedForExistence()
	{
		$identityMap = new IdentityMap();

		$this->assertFalse(isset($identityMap[42]));

		$identityMap[42] = 'User';

		$this->assertTrue(isset($identityMap[42]));
	}

}
