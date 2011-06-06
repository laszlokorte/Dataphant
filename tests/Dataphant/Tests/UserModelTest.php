<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;
use Dataphant\ModelBase;

/*
 * UserModelTest
 */
class UserModelTest extends BaseTestCase
{

	public function testPropertiesInsideModel()
	{
		User::defineProperty('nickname');

		$user = User::build();

		$user->setNick('SCHIRI');
		$this->assertSame('SCHIRI', $user->getNick());
		$this->assertSame('SCHIRI', $user->nickname);
		$this->assertSame('SCHIRI', $user->getNickViaArray());

		$attributes = $user->getAttributes();
		$this->assertSame('SCHIRI', $attributes['nickname']);

		$this->assertSame(User::nickname(), User::nick());
	}

}

class User extends ModelBase
{

	public static function nick()
	{
		return static::nickname();
	}

	public function getNick()
	{
		return $this->nickname;
	}

	public function setNick($nick)
	{
		$this->nickname = $nick;
	}

	public function getNickViaArray()
	{
		return $this['nickname'];
	}

}