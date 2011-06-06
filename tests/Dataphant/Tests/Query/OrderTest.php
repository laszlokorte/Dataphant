<?php

namespace Dataphant\Tests\Query;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Query\Order;

class OrderTest extends BaseTestCase
{

	public function setUp()
	{
		$model = 'User';
		$propertyName = 'nickname';
		$this->property = $this->getMock(
			'Dataphant\Properties\PropertyBase', array('phpunitbug'), array($model, $propertyName)
		);
	}

	public function testAscIsValidDirection()
	{
		$direction = 'asc';
		$order = new Order($this->property, $direction);

		$this->assertSame($this->property, $order->getProperty());
		$this->assertSame($direction, $order->getDirection());
	}

	public function testDescIsValidDirection()
	{
		$direction = 'desc';
		$order = new Order($this->property, $direction);

		$this->assertSame($this->property, $order->getProperty());
		$this->assertSame($direction, $order->getDirection());
	}

	public function testInvalidDirectionsAreNotAllowed()
	{
		$this->setExpectedException('Dataphant\Query\Exceptions\InvalidDirectionException');

		$direction = 'upsidedown';
		$order = new Order($this->property, $direction);
	}

}
