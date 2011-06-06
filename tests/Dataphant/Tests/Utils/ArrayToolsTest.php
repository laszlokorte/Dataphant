<?php

namespace Dataphant\Tests\Utils;

use Dataphant\Tests\BaseTestCase;
use Dataphant\Utils\ArrayTools;

use StdClass;

class ArrayToolsTest extends BaseTestCase
{


	public function testScalarGetsConvertedIntoArray()
	{
		$newArray = ArrayTools::flatten(42);
		$this->assertSame(array(42), $newArray);
	}

	public function testObjectGetsConvertedIntoArray()
	{
		$obj = new StdClass();

		$newArray = ArrayTools::flatten($obj);
		$this->assertSame(array($obj), $newArray);
	}

	public function testFlatArrayDoesNotGetChanged()
	{
		$flatArray = array('a','b','c');

		$newArray = ArrayTools::flatten($flatArray);
		$this->assertSame($flatArray, $newArray);
	}

	public function testDeepArrayGetsFlattened()
	{
		$flatArray = array('a','b','c',array('d','e','x' => array('f','g')));

		$newArray = ArrayTools::flatten($flatArray);
		$this->assertSame(array('a','b','c','d','e','f','g'), $newArray);


		$flatArray = array(array('a'),array('b'),array('c'),array('d'), array('d'));

		$newArray = ArrayTools::flatten($flatArray);
		$this->assertSame(array('a','b','c','d','d'), $newArray);
	}

	public function testGetOrDefault()
	{
		$array = array(
			'a' => 'HTTP',
			'b' => 'FTP'
		);

		$this->assertSame('HTTP', ArrayTools::getOrDefault($array, 'a'));
		$this->assertSame('FTP', ArrayTools::getOrDefault($array, 'b'), 'POP');
		$this->assertSame(NULL, ArrayTools::getOrDefault($array, 'c'));
		$this->assertSame(NULL, ArrayTools::getOrDefault($array, 7));
		$this->assertSame('HTTPS', ArrayTools::getOrDefault($array, 'c', 'HTTPS'));
	}

}
