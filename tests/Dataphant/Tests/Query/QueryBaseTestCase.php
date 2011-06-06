<?php

namespace Dataphant\Tests\Query;

use Dataphant\Tests\BaseTestCase;
use Dataphant\Query\Comparisons\ComparisonBase;

use Dataphant\Adapters\SqliteAdapter;
use Dataphant\DataSource;


class QueryBaseTestCase extends BaseTestCase
{
	public function setUp()
	{
		$this->property = $this->getMock('Dataphant\Properties\PropertyInterface');
		$this->record = $this->getMock('Dataphant\\RecordInterface');
		$this->adapter = new SqliteAdapter('default');
		$this->adapter->setDebugMode(TRUE);
		$this->dataSource = DataSource::getByName('default');
	}

	protected function getFakeModel()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('user');
		return $model;
	}
}
