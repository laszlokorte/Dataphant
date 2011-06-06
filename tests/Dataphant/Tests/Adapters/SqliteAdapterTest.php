<?php

namespace Dataphant\Tests\Adapters;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Adapters\SqliteAdapter;
use Dataphant\DataSource;

use Dataphant\Query\Operations\AndOperation;

class SqliteAdapterTest extends BaseTestCase
{

	protected function getAdapter($options=array())
	{
		$adapter = new SqliteAdapter('default', $options);
		$adapter->setDebugMode(TRUE);
		return $adapter;
	}

	public function testNameGetsSetOnInitialization()
	{
		$name = 'name';

		$adapter = new SqliteAdapter('name');

		$this->assertSame($name, $adapter->getName());
	}


	public function testOptionsCanBePassedOnInitialization()
	{
		$options = array('username' => 'root');

		$adapter = new SqliteAdapter('name', $options);

		$this->assertAttributeContains('root', 'options', $adapter);
		$this->assertAttributeNotContains('foo', 'options', $adapter);
	}

	public function testDefaultEncondingIsUtf8()
	{
		$options = array('encoding' => 'iso');

		$adapter = new SqliteAdapter('name', $options);

		$options = $this->getReflectionAttribute($adapter, 'options');

		$this->assertSame('iso', $options['encoding']);
	}

	public function testDefaultOptionsCanBeOverwritten()
	{
		$options = array('username' => 'root');

		$adapter = new SqliteAdapter('name', $options);

		$this->assertAttributeContains('root', 'options', $adapter);
		$this->assertAttributeNotContains('foo', 'options', $adapter);
	}

	public function testQueryCanBeFactored()
	{
		$adapter = $this->getAdapter();
		$dataSource = $this->getMock('Dataphant\DataSourceInterface');

		$model = $this->getMockClass('Dataphant\ModelBase', array(uniqid('method')));
		$options = array();

		$query = $adapter->getNewQuery($dataSource, $model, $options);

		$this->assertInstanceOf('Dataphant\Query\Query', $query);
	}

	public function testDebugModeIsDisabledByDefault()
	{
		$adapter = new SqliteAdapter('default');

		$this->assertFalse($adapter->getDebugMode());
	}

	public function testDebugModeCanBeEnabled()
	{
		$adapter = new SqliteAdapter('default');

		$adapter->setDebugMode(TRUE);

		$this->assertTrue($adapter->getDebugMode());
	}

	public function testLastExecutedStatementGetsSet()
	{
		$adapter = $this->getAdapter();

		$stm = 'DELETE FROM users WHERE id = 5';
		$adapter->execute($stm);
		$this->assertSame($stm, $adapter->getLastStatement());

		$otherStm = 'DELETE FROM users WHERE id = 5';
		$adapter->execute($otherStm);
		$this->assertSame($otherStm, $adapter->getLastStatement());
	}

	public function testAnonymBindingsGetQuotedOnStatemenExecution()
	{
		$adapter = $this->getAdapter();

		$adapter->execute('SELECT * FROM users WHERE nickname = ? AND password = ? OR token = :3 AND ?', array("josh13", "se'';--c'ret", '123'));

		$this->assertSame("SELECT * FROM users WHERE nickname = 'josh13' AND password = 'se'''';--c''ret' OR token = '123' AND '123'", $adapter->getLastStatement());
	}

	public function testNamedBindingsGetQuotedOnStatemenExecution()
	{
		$adapter = $this->getAdapter();

		$adapter->execute('SELECT * FROM users WHERE nickname = :nickname AND password = :password',
		array('nickname' => "peter", 'password' => "se'';--c'ret"));

		$this->assertSame("SELECT * FROM users WHERE nickname = 'peter' AND password = 'se'''';--c''ret'", $adapter->getLastStatement());
	}

	public function testCollectionCanBeSelected()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $adapter->getNewQuery($dataSource, $model);
		$query->setLimit(30);
		$query->setOffset(30);
		$props = $model::getProperties();
		$query->setConditions(new AndOperation(array($props['nickname']->like('Las%'), $model::email()->eq('abc'), $model::nickname()->eq('Jokey'))));
		$query->setFields(array('id', 'nickname', 'email'));
		$query->setOrder(array($props['nickname']->asc()));

		$adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", ";
		$sql .= "\"users\".\"nickname\" AS \"nickname\", ";
		$sql .= "\"users\".\"email\" AS \"email\" ";
		$sql .= "FROM \"users\" ";
		$sql .= "WHERE ((\"users\".\"nickname\" LIKE 'Las%') ";
		$sql .= "AND (\"users\".\"email\" = 'abc') ";
		$sql .= "AND (\"users\".\"nickname\" = 'Jokey')) ";
		$sql .= "ORDER BY \"users\".\"nickname\" ASC ";
		$sql .= "LIMIT 30 OFFSET 30";
		$this->assertSame($sql, $adapter->getLastStatement());
	}


	public function testCollectionCanBeSelectedWithoutOrdering()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $adapter->getNewQuery($dataSource, $model);
		$query->setLimit(30);
		$query->setOffset(30);
		$props = $model::getProperties();
		$query->setConditions(new AndOperation(array($props['nickname']->like('Las%'), $model::email()->eq('abc'), $model::nickname()->eq('Jokey'))));
		$query->setFields(array('id', 'nickname', 'email'));

		$adapter->read($query);
		$sql  = "SELECT ";
		$sql .= "\"users\".\"id\" AS \"id\", ";
		$sql .= "\"users\".\"nickname\" AS \"nickname\", ";
		$sql .= "\"users\".\"email\" AS \"email\" ";
		$sql .= "FROM \"users\" ";
		$sql .= "WHERE ((\"users\".\"nickname\" LIKE 'Las%') ";
		$sql .= "AND (\"users\".\"email\" = 'abc') ";
		$sql .= "AND (\"users\".\"nickname\" = 'Jokey')) ";
		$sql .= "LIMIT 30 OFFSET 30";
		$this->assertSame($sql, $adapter->getLastStatement());
	}

	public function testRecordsCanBeInserted()
	{

		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$record = $model::build();

		$record->setAttribute('email', 'peter');

		$adapter->create(array($record));


		$sql = "INSERT INTO \"users\" (\"email\") VALUES ('peter')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}


	public function testRecordsCanBeInsertedWithGivenSerial()
	{

		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$record = $model::build();

		$record->id = 50;
		$record->nickname = 'Walter';

		$adapter->create(array($record));


		$sql = "INSERT INTO \"users\" (\"id\", \"nickname\") VALUES (50, 'Walter')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}

	public function testNullValuesAreIngoredInInsertionIfNoDefaultValueGiven()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$record = $model::build();

		$record->id = 50;
		$record->nickname = 'Walter';
		$record->email = NULL;

		$adapter->create(array($record));


		$sql = "INSERT INTO \"users\" (\"id\", \"nickname\") VALUES (50, 'Walter')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}


	public function testDefaultValuesAreUsedOnInsertion()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname', array('default' => 'Herbert'));
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$record = $model::build();

		$record->id = 50;
		$record->nickname = NULL;

		$adapter->create(array($record));


		$sql = "INSERT INTO \"users\" (\"id\", \"nickname\") VALUES (50, 'Herbert')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}


	public function testSerialGetsIgnoredWhenItsNull()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname', array('default' => 'Herbert'));
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$record = $model::build();

		$record->id = NULL;
		$record->nickname = 'Bina';

		$adapter->create(array($record));


		$sql = "INSERT INTO \"users\" (\"nickname\") VALUES ('Bina')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}


	public function testCollectionWithConditionsCanBeDeleted()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $adapter->getNewQuery($dataSource, $model);
		$props = $model::getProperties();
		$collection = new \Dataphant\Collection($model, $query);
		$collection = $collection->filter(new AndOperation(array($props['nickname']->like('Las%'), $model::email()->eq('abc'), $model::nickname()->eq('Jokey'))));

		$adapter->delete($collection);

		$sql = "DELETE FROM \"users\" WHERE ((\"users\".\"nickname\" LIKE 'Las%') AND (\"users\".\"email\" = 'abc') AND (\"users\".\"nickname\" = 'Jokey'))";

		$this->assertSame($sql, $adapter->getLastStatement());
	}

	public function testCollectionWithConditionsCanBeUpdated()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $adapter->getNewQuery($dataSource, $model);
		$props = $model::getProperties();
		$collection = new \Dataphant\Collection($model, $query);
		$collection = $collection->filter(new AndOperation(array($props['nickname']->eq('Sniper'))));

		$attributes = array(
			'description' => 'He rules them all',
		);

		$adapter->update($attributes, $collection);

		$sql = "UPDATE \"users\" SET \"description\"='He rules them all' WHERE ((\"users\".\"nickname\" = 'Sniper'))";

		$this->assertSame($sql, $adapter->getLastStatement());
	}

	public function testSchemaCanBeCreated()
	{
		$adapter = $this->getAdapter();

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::defineProperty('password');
		$model::defineProperty('description', array('type' => 'Text'));
		$model::setEntityName('User');

		$adapter->createDataSchema($model);

		$sql = 'CREATE TABLE "users" ("id" INTEGER, "nickname" VARCHAR(50), "email" VARCHAR(50), "password" VARCHAR(50), "description" TEXT, CONSTRAINT "users_PK" PRIMARY KEY (id))';
		$this->assertSame($sql, $adapter->getLastStatement());
	}

	public function testNumericValuesAreNotAffectedByQuoting()
	{
		$adapter = $this->getAdapter();
		$this->assertSame(23, $adapter->quote(23));
	}

	public function testNullValuesGetConvertedToStringButNotQuoted()
	{
		$adapter = $this->getAdapter();
		$this->assertSame('NULL', $adapter->quote(NULL));
	}

	public function testStringValuesGetConvertedToStringButNotQuoted()
	{
		$adapter = $this->getAdapter();
		$this->assertSame("'some ''val\"ue'", $adapter->quote("some 'val\"ue"));
	}

	public function testConnectionCanBeEstablished()
	{
		$adapter = $this->getAdapter(array('filename' => ':memory:'));
		$this->assertInstanceOf("PDO", $adapter->getConnection());
	}

	public function testConnectionCanBeEstablishedWithDefaultValues()
	{
		$this->markTestIncomplete('Not yet implemented!');
		$adapter = $this->getAdapter();
		$adapter->getConnection();
	}

	public function testTablePrefixCanBeUsed()
	{
		$adapter = $this->getAdapter(array('prefix' => 'cs'));

		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('id', array('type' => 'Serial'));
		$model::defineProperty('nickname');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $adapter->getNewQuery($dataSource, $model);
		$query->setConditions($model::nickname()->eq('Mulder'));

		$adapter->read($query);
		$sql  = "SELECT \"cs_users\".\"id\" AS \"id\", \"cs_users\".\"nickname\" AS \"nickname\" FROM \"cs_users\" WHERE (\"cs_users\".\"nickname\" = 'Mulder')";
		$this->assertSame($sql, $adapter->getLastStatement());
	}
}
