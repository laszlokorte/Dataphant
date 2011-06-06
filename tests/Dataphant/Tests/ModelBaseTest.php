<?php

namespace Dataphant\Tests;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Utils\Logger;

use Dataphant\ModelBase;
use Dataphant\DataSource;

class ModelBaseTest extends BaseTestCase
{

	public function setUp()
	{
		$output = FALSE;
		$adapter = new \Dataphant\Adapters\SqliteAdapter('default', array('logger' => new Logger('php://output', Logger::INFO, $output)));
		$registry = \Dataphant\AdapterRegistry::getInstance();
		$registry->registerAdapter($adapter);
	}

	public function tearDown()
	{
		\Dataphant\AdapterRegistry::clearInstance();
		\Dataphant\DataSource::resetByName('default');
	}

	public function testDataSourceIsDefined()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$this->assertInstanceOf('Dataphant\\DataSourceInterface', $modelClass::getDataSource());
	}


	public function testEntityNameIsModelsClassNameWithOutNamespace()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$entityName = $modelClass::getEntityName();
		$className = explode('\\', $modelClass);
		$this->assertSame(end($className), $entityName);
	}

	public function testEntityNameCanBeSet()
	{
		$entityName = 'User';
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$modelClass::setEntityName($entityName);

		$this->assertSame($entityName, $modelClass::getEntityName());
	}

	public function testPropertyCanBeRegistered()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));


		$modelClass::defineProperty('nickname');

		$properties = $modelClass::getProperties();
		$this->assertTrue(is_array($properties));
		$this->assertTrue(isset($properties['nickname']));
		$this->assertInstanceOf('Dataphant\\Properties\\PropertyInterface', $properties['nickname']);
	}

	public function testSinglePropertyCanBeRegisteredAsKey()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));


		$modelClass::defineProperty('nickname', array('key' => TRUE));
		$keys = $modelClass::getKeys();

		$this->assertSame(1, count($keys));
		$this->assertSame('nickname', $keys['nickname']->getName());
	}

	public function testPropertiesDefaultTypeIsString()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$modelClass::defineProperty('id', array('type' => 'Serial'));
		$modelClass::defineProperty('nickname');

		$properties = $modelClass::getProperties();
		$this->assertInstanceOf('Dataphant\\Properties\\StringProperty', $properties['nickname']);
	}

	public function testPropertyCanBeRegisteredWithSpecificType()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$modelClass::defineProperty('id', array('type' => 'Serial'));
		$modelClass::defineProperty('nickname', array('type' => 'String'));

		$properties = $modelClass::getProperties();
		$this->assertTrue(is_array($properties));
		$this->assertTrue(isset($properties['nickname']));
		$this->assertInstanceOf('Dataphant\\Properties\\StringProperty', $properties['nickname']);
	}

	public function testPropertyCanNotBeRegisteredTwice()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$oldProp = $modelClass::defineProperty('nickname');
		$this->assertSame($oldProp, $modelClass::nickname());

		$this->setExpectedException('Exception');
		$modelClass::defineProperty('nickname');
	}

	public function testNoRelationshipsAreRegisteredByDefault()
	{
		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$this->assertSame(0, count($userClass::getRelationships()));
	}

	public function testOneToManyRelationshipCanBeRegisteredWithOptions()
	{
		$relationshipName = 'comments';

		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$commentClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$userClass::hasMany($relationshipName, array('class' => $commentClass));

		$relationships = $userClass::getRelationships();

		$this->assertInstanceOf('Dataphant\\Relationships\\OneToManyRelationship', $relationships[$relationshipName]);
	}

	public function testParentOneToOneRelationshipCanBeRegisteredWithOptions()
	{
		$relationshipName = 'profile';

		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$profileClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$userClass::hasOne($relationshipName, array('class' => $profileClass));

		$relationships = $userClass::getRelationships();

		$this->assertInstanceOf('Dataphant\\Relationships\\OneToOneRelationship', $relationships[$relationshipName]);
	}

	public function testChildManyOrOneToOneRelationshipCanBeRegisteredWithOptions()
	{
		$relationshipName = 'user';

		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$profileClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$profileClass::defineProperty('user_id', array('type' => 'Integer'));
		$profileClass::belongsTo($relationshipName, array('class' => $userClass));

		$relationships = $profileClass::getRelationships();

		$this->assertInstanceOf('Dataphant\\Relationships\\ManyToOneRelationship', $relationships[$relationshipName]);
	}

	public function testManyToManyRelationshipCanBeRegisteredWithOptions()
	{
		$relationshipName = 'groups';

		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$groupClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$userClass::HasAndbelongsToMany($relationshipName, array('class' => $groupClass));

		$relationships = $userClass::getRelationships();

		$this->assertInstanceOf('Dataphant\\Relationships\\ManyToManyRelationship', $relationships[$relationshipName]);
	}

	public function testStaticMethodCallReturnsMatchingPropertyIfRegistered()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$modelClass::defineProperty('nickname');
		$nickname = $modelClass::nickname();

		$this->assertInstanceOf('Dataphant\\Properties\\PropertyInterface', $nickname);
		$this->assertSame($modelClass, $nickname->getModel());
	}

	public function testStaticMethodCallReturnsNewPathIfMatchingRelationshipIsRegistered()
	{
		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$commentClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$userClass::hasMany('comments', array('class' => $commentClass));

		$commentsRelationship = $userClass::comments();
		$this->assertInstanceOf('Dataphant\\Query\\PathInterface', $commentsRelationship);
		$this->assertSame($userClass, $commentsRelationship->getLastRelationship()->getSourceModel());
		$this->assertNull($commentsRelationship->getProperty());
	}

	public function testCallingSomeStupidStaticMethodThrowsAnException()
	{
		$this->setExpectedException('Exception');

		$userClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$userClass::foobar();
	}

	public function testStaticFindMethodReturnsCollection()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$collection = $modelClass::find();

		$this->assertInstanceOf('Dataphant\\CollectionInterface', $collection);
	}

	public function testScopeCanBeDefinedAsCollection()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$collection = $modelClass::find();

		$modelClass::defineScope('activeUsers', $collection);

		#TODO: test have to be improved
		$this->assertInstanceOf('Dataphant\\CollectionInterface', $modelClass::getScope('activeUsers'));
	}

	public function testScopeCanBeDefinedAsClosure()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$collection = $this->getMock('Dataphant\\CollectionInterface');

		$modelClass::defineScope('activeUsers', function() use($collection) {
			return $collection;
		});

		$this->assertSame($collection, $modelClass::getScope('activeUsers'));
	}

	public function testScopeCanNotBeCollectionOfAnOtherModel()
	{
		$this->setExpectedException('Exception');

		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$modelClass::defineScope('activeUsers', 'foo');
	}

	public function testRecordKnowsItsModel()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$record = $modelClass::build();

		$this->assertSame($modelClass, $record->getModel());
	}

	public function testRecordCanBeBuilt()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$record = $modelClass::build();
		$anotherRecord = $modelClass::build();

		$this->assertInstanceOf($modelClass, $record);

		$this->assertNotSame($record, $anotherRecord);
	}

	public function testRecordsStateIsNewAfterInitialization()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$record = $modelClass::build();

		$this->assertTrue($record->isNew());
	}

	public function testTheSerialIsNamedIdByDefault()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$serial = $modelClass::getSerial();

		$this->assertInstanceOf('Dataphant\\Properties\\PropertyInterface', $serial);
		$this->assertSame('id', $serial->getName());
		$this->assertTrue($serial->isSerial());
	}

	public function testTheSerialCanBeSet()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$modelClass::defineProperty('name', array('type' => 'Serial'));
		$serial = $modelClass::getSerial();

		$this->assertInstanceOf('Dataphant\\Properties\\PropertyInterface', $serial);
		$this->assertSame('name', $serial->getName());
		$this->assertTrue($serial->isSerial());
	}

	public function testSerialCanNotBeSetTwice()
	{
		$this->setExpectedException('Exception');
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$modelClass::defineProperty('name', array('type' => 'Serial'));
		$modelClass::defineProperty('foobar', array('type' => 'Serial'));
	}

	public function testGettingAnNewRecordsAttributeReturnsThePropertiesDefaultValue()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$modelClass::defineProperty('age', array('default' => 20));

		$record = $modelClass::build();

		$this->assertSame(20, $record->getAttribute('age'));
	}

	public function testGettingAnNewRecordsAttributeReturnsNullWhenNoDefaultIsSet()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$modelClass::defineProperty('age');

		$record = $modelClass::build();

		$this->assertSame(NULL, $record->getAttribute('age'));
	}

	public function testARecordsAttributeToBeGetHaveToBeARegisteredProperty()
	{
		$this->setExpectedException('Exception');

		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$record = $modelClass::build();
		$record->getAttribute('foobar');
	}

	public function testANewRecordsStateIsTransient()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$record = $modelClass::build();

		$this->assertInstanceOf('Dataphant\States\TransientState', $record->getState());
	}

	public function testRecordsAttributeCanBeSet()
	{
		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$modelClass::defineProperty('age');

		$record = $modelClass::build();

		$record->setAttribute('age', 99);
		$this->assertSame(99, $record->getAttribute('age'));
	}

	public function testARecordsAttributeToBeSetHaveToBeARegisteredProperty()
	{
		$this->setExpectedException('Exception');

		$modelClass = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$record = $modelClass::build();
		$record->setAttribute('foo', 'bar');
	}

	public function testRowsCanBeMapped()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');

		$query = $dataSource->getNewQuery($model);
		$query->setReload(TRUE);

		$rows = array(
			array('id' => 1, 'nickname' => 'SCHIRI', 'email' => 'blong'),
			array('id' => 2, 'nickname' => 'duRiel', 'email' => 'bla'),
			array('id' => 3, 'nickname' => 'Jokey', 'email' => 'blub'),
		);

		$records = $model::map($rows, $query);

		$this->assertSame('SCHIRI', $records[0]->getAttribute('nickname'));
		$this->assertSame('duRiel', $records[1]->getAttribute('nickname'));
		$this->assertSame('Jokey', $records[2]->getAttribute('nickname'));
	}


	public function testObjectsAreJustCreatedOncePerRow()
	{
		$model = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$model::defineProperty('nickname');
		$model::defineProperty('email');
		$model::setEntityName('User');

		$dataSource = DataSource::getByName('default');
		$identityMap = $dataSource->getIdentityMap($model);
		$identityMap['id:3'] = $model::build();

		$query = $dataSource->getNewQuery($model);
		$query->setReload(TRUE);

		$rows = array(
			array('id' => 3, 'nickname' => 'Jokey', 'email' => 'blub'),
		);

		$records = $model::map($rows, $query);

		$this->assertSame($identityMap['id:3'], $records[0]);
	}

	public function testSomeFreakingStuff()
	{
		/*
			TODO: Clean this up
		*/


		/*
		Setup the model
		*/
		$userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$userModel::defineProperty('nickname');
		$userModel::defineProperty('age', array('type' => 'Integer'));
		$userModel::defineProperty('description', array('type' => 'Text'));
		$userModel::setEntityName('User');

		$commentModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$commentModel::defineProperty('content', array('type' => 'Text'));
		$commentModel::defineProperty('user_id', array('type' => 'Integer'));
		$commentModel::belongsTo('user', array('class' => $userModel));
		$commentModel::setEntityName('Comment');

		$userModel::hasMany('comments', array('class' => $commentModel));

		/*
		Create the schema in the database
		*/
		$userModel::getDataSource()->getAdapter()->createDataSchema($userModel);
		$userModel::getDataSource()->getAdapter()->createDataSchema($commentModel);

		/*
		Do some manuel insert to have some data to work with
		*/
		$connection = $userModel::getDataSource()->getAdapter()->getConnection();
		$stm = $connection->exec('INSERT INTO users (nickname, age) VALUES (\'SCHIRI\', 29)');
		$stm = $connection->exec('INSERT INTO users (nickname, age) VALUES (\'Micha\', 29)');
		$stm = $connection->exec('INSERT INTO users (nickname, age) VALUES (\'Markus\', 29)');
		$stm = $connection->exec('INSERT INTO users (nickname, age) VALUES (\'SCHajo\', 29)');

		/*
		----------------------------------------
		Records can be read from the dataSource
		*/
		$collection = $userModel::find();
		$collection = $collection->filter($userModel::nickname()->like('SCH%')->and_($userModel::age()->gt(20)));
		$collection = $collection->limit(20);
		$i=0;
		foreach($collection AS $user)
		{
			$this->assertSame($user, $collection[$i]);
			$this->assertInstanceof($userModel, $user);
			$this->assertSame(29, $user->getAttribute('age'));
			$this->assertTrue($user->isClean());
			$i++;
		}
		$this->assertSame(2, $i);
		$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" WHERE ((\"users\".\"nickname\" LIKE 'SCH%') AND (\"users\".\"age\" > 20)) LIMIT 20";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		/*
		------------------------------------------
		Records can be updated
		*/

		$userOne = $collection[1];
		$this->assertTrue($user->isClean());
		$userOne->nickname = 'HubaBuba';
		$this->assertFalse($user->isClean());
		$this->assertTrue($user->isDirty());
		$userOne->save();
		$this->assertTrue($user->isClean());

		$sql = 'UPDATE "users" SET "users"."nickname"=\'HubaBuba\' WHERE (("users"."id" = 4))';
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$userOne->id = 23;
		$userOne->save();

		$identityMap = $userModel::getDataSource()->getIdentityMap($userModel);
		$this->assertFalse(isset($identityMap['id:4']));
		$this->assertTrue(isset($identityMap['id:23']));

		$sql = 'UPDATE "users" SET "users"."id"=23 WHERE (("users"."id" = 4))';
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());


		/*
		-----------------------------------------
		Records can be created
		*/
		$someNewUser = $userModel::build();
		$someNewUser->nickname = 'Paul';
		$someNewUser->age = 23;

		$this->assertSame('Paul', $someNewUser->nickname);
		$result = $someNewUser->save();
		$this->assertTrue($result);

		$sql = "INSERT INTO \"users\" (\"nickname\", \"age\") VALUES ('Paul', 23)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
		$this->assertTrue($someNewUser->isClean());
		$this->assertFalse($someNewUser->isNew());
		$this->assertSame(5, $someNewUser->id);

		$this->assertTrue(isset($identityMap['id:5']));


		/*
		-----------------------------------------
		Created record can be updated afterwards
		*/
		$someNewUser->nickname = 'Peter';
		$this->assertTrue($someNewUser->isDirty());
		$result = $someNewUser->save();
		$this->assertTrue($result);
		$this->assertTrue($someNewUser->isClean());
		$sql = "UPDATE \"users\" SET \"users\".\"nickname\"='Peter' WHERE ((\"users\".\"id\" = 5))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());


		/*
		-----------------------------------------
		Created record can be destroyed
		*/
		$someNewUser->destroy();
		$this->assertTrue($someNewUser->isDestroyed());
		$sql = "DELETE FROM \"users\" WHERE ((\"users\".\"id\" = 5))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$userStillThere = $connection->query('SELECT * FROM users WHERE id = 5');
		$this->assertEmpty($userStillThere->fetchAll());


		/*
		-----------------------------------------
		Record can be destroyed before he got saved at all
		*/
		$userNotWanted = $userModel::build();
		$userNotWanted->destroy();


		/*
		-----------------------------------------
		Record can be resetted
		*/
		$user = $userModel::find()->first();
		$oldNickname = $user->nickname;
		$user->nickname = 'Heinz';
		$this->assertTrue($user->isDirty());
		$user->reload();
		$this->assertFalse($user->isDirty());
		$this->assertSame($oldNickname, $user->nickname);


		/*
		-----------------------------------------
		Lazy property can be set
		*/
		$this->assertFalse($user->isAttributeLoaded('description'));

		$user->description = 'Ein cooler User';

		$this->assertTrue($user->isAttributeLoaded('description'));

		$user->save();

		$sql = "UPDATE \"users\" SET \"users\".\"description\"='Ein cooler User' WHERE ((\"users\".\"id\" = 1))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());


		/*
		-----------------------------------------
		Record can be inserted into collection
		*/

		$collection = $userModel::find()->filter($userModel::age()->eq(42));
		$oldLength = count($collection);
		$yetAnotherUser = $userModel::build();
		$collection[] = $yetAnotherUser;

		$this->assertContains($yetAnotherUser, $collection);
		$this->assertSame($oldLength+1, count($collection));

		$johnLocke = $userModel::build();
		$oldLength = count($collection);
		$collection[0] = $johnLocke;
		$this->assertSame($oldLength+1, count($collection));
		$this->assertSame(42, $johnLocke->age);
		$johnLocke->save();

		/*
		----------------------------------------
		*/

		$comment = $commentModel::build(array('content' => 'Das ist toll!', 'user' => $user));

		$this->assertSame($user, $comment->user);
		$comment->save();

		$sql = "INSERT INTO \"comments\" (\"content\", \"user_id\") VALUES ('Das ist toll!', 1)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$this->assertSame($user, $comment->user);

		$comment->user = $johnLocke;
		$comment->save();

		$sql = "UPDATE \"comments\" SET \"comments\".\"user_id\"=6 WHERE ((\"comments\".\"id\" = 1))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());


		/*
		----------------------------------------
		*/

		$otherComment = $commentModel::build(array('content' => 'Das ist toll!', 'user_id' => 3));
		$otherComment->save();

		$sql = "INSERT INTO \"comments\" (\"content\", \"user_id\") VALUES ('Das ist toll!', 3)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$sql = $userModel::getDataSource()->getAdapter()->getLastStatement();

		$otherComment->user;

		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
		$this->assertSame($userModel::find()->get(3), $otherComment->user);

		/*
		----------------------------------------
		*/

		$peter = $userModel::find()->first();
		count($peter->comments);

		$sql = "SELECT \"comments\".\"id\" AS \"id\", \"comments\".\"user_id\" AS \"user_id\" FROM \"comments\" WHERE (\"comments\".\"user_id\" = 1)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$wuhuComment = $commentModel::build(array('content' => 'blaaaaa'));
		$peter->comments[] = $wuhuComment;

		$this->assertSame($peter->id, $wuhuComment->user_id);

		$wuhuComment->user = array('nickname' => 'Nancy', 'age' => 108);

		$this->assertSame('Nancy', $wuhuComment->user->nickname);
		$this->assertNull($wuhuComment->user->id);

		$wuhuComment->user->save();

		$this->assertInternalType('integer', $wuhuComment->user->id);

		$myUser = $wuhuComment->user;

		$myUser->comments[] = array('content' => 'Wuuuuuuuusaaaaaaaaa!!!');
		$myUser->comments[] = array('content' => 'Wusarammdammdamm!!!');
		$myUser->comments[] = array('content' => 'I like Webspell');

		$this->assertSame('Wuuuuuuuusaaaaaaaaa!!!', $myUser->comments[0]->content);

		$this->assertSame(3, count($myUser->comments));

		$this->assertTrue($myUser->comments->isDirty());

		$myUser->comments->save();

		$this->assertTrue($myUser->comments->isClean());

		$searchComments = $commentModel::find()->filter($commentModel::content()->eq('Wusarammdammdamm!!!'));

		$this->assertSame(1, count($searchComments));


		/*
		------------------------------------------------
		*/

		# All users who have written a comment with the content "Wusarammdammdamm!"
		$complexUserQueryOne = $userModel::find()->filter($userModel::comments()->content()->eq('Wusarammdammdamm!!!'));

		$this->assertSame(1, count($complexUserQueryOne));

		$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" INNER JOIN \"comments\" AS \"user_comments\" ON (\"user_comments\".\"user_id\" = \"users\".\"id\") WHERE (\"user_comments\".\"content\" = 'Wusarammdammdamm!!!')";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());


		# All users who have written a comment with the content "I like DZCP"
		$complexUserQueryTwo = $userModel::find()->filter($userModel::comments()->content()->eq('I like DZCP'));

		$this->assertSame(0, count($complexUserQueryTwo));


		$this->assertSame(3, count($complexUserQueryOne[0]->comments));

		$this->assertSame('Nancy', $complexUserQueryOne[0]->nickname);


		/*
		------------------------------------------------
		*/


		$replyModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$replyModel::setEntityName('Reply');
		$replyModel::defineProperty('text', array('type' => 'Text'));
		$replyModel::defineProperty('comment_id', array('type' => 'Integer'));

		$replyModel::belongsTo('comment', array('class' => $commentModel));
		$commentModel::hasMany('replies', array('class' => $replyModel));

		$userModel::getDataSource()->getAdapter()->createDataSchema($replyModel);

		$usersAgreeing = $userModel::find()->filter($userModel::comments()->replies()->text()->eq('me too'));
		count($usersAgreeing);

		$repliesToUserX = $replyModel::find()->filter($replyModel::comment()->user()->comments()->content()->eq('I like Webspell'));
		count($repliesToUserX);
		$sql = "SELECT \"replys\".\"id\" AS \"id\", \"replys\".\"comment_id\" AS \"comment_id\" FROM \"replys\" INNER JOIN \"comments\" AS \"replies_comments\" ON (\"replies_comments\".\"id\" = \"replys\".\"comment_id\") INNER JOIN \"users\" AS \"comments_users\" ON (\"comments_users\".\"id\" = \"replies_comments\".\"user_id\") INNER JOIN \"comments\" AS \"user_comments\" ON (\"user_comments\".\"user_id\" = \"comments_users\".\"id\") WHERE (\"user_comments\".\"content\" = 'I like Webspell')";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$repliesToUserX = $replyModel::find()->filter($replyModel::comment()->user()->comments()->content()->eq('I like Webspell')->or_($replyModel::comment()->user()->id()->in(array(1,2,3))->and_($replyModel::comment()->content()->like('%php%'))));
		count($repliesToUserX);
		$sql = "SELECT \"replys\".\"id\" AS \"id\", \"replys\".\"comment_id\" AS \"comment_id\" FROM \"replys\" INNER JOIN \"comments\" AS \"replies_comments\" ON (\"replies_comments\".\"id\" = \"replys\".\"comment_id\") INNER JOIN \"users\" AS \"comments_users\" ON (\"comments_users\".\"id\" = \"replies_comments\".\"user_id\") INNER JOIN \"comments\" AS \"user_comments\" ON (\"user_comments\".\"user_id\" = \"comments_users\".\"id\") WHERE ((\"user_comments\".\"content\" = 'I like Webspell') OR ((\"comments_users\".\"id\" IN (1, 2, 3)) AND (\"replies_comments\".\"content\" LIKE '%php%')))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$repliesToUserX = $replyModel::find()->filter($replyModel::comment()->user()->id()->in(array(1,2,3))->or_($replyModel::comment()->user()->comments()->content()->eq('I like Webspell')));
		count($repliesToUserX);

		/*
		------------------------------------------------
		*/

		$commentModel::build(array('id' => 108, 'user_id' => 1))->save();
		$commentModel::build(array('id' => 109, 'user_id' => 2))->save();
		$commentModel::build(array('id' => 110, 'user_id' => 3))->save();

		$replyModel::build(array('comment_id' => 108, 'text' => 'abc'))->save();
		$replyModel::build(array('comment_id' => 108, 'text' => 'def'))->save();
		$replyModel::build(array('comment_id' => 109, 'text' => 'ghi'))->save();
		$replyModel::build(array('comment_id' => 109, 'text' => 'jkl'))->save();
		$replyModel::build(array('comment_id' => 110, 'text' => 'mno'))->save();
		$replies = $replyModel::find()->all();


		foreach($replies AS $key => $reply)
		{
			$reply->comment;
		}

		$sql = "SELECT \"replys\".\"id\" AS \"id\", \"replys\".\"comment_id\" AS \"comment_id\" FROM \"replys\" WHERE 1";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		$userModel::defineScope('adults', $userModel::find()->filter($userModel::age()->gte(18)));
		$userModel::defineScope('alphabet', $userModel::find()->orderBy($userModel::nickname()->asc()));
		$userModel::defineScope('byAge', $userModel::find()->orderBy($userModel::age()->asc()));
		$userModel::defineScope('skip30', $userModel::find()->skip(30));


		count($userModel::find()->adults());
		$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" WHERE (\"users\".\"age\" >= 18)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());



		$reportModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$reportModel::setEntityName('Report');
		$reportModel::defineProperty('text', array('type' => 'Text'));
		$reportModel::defineProperty('reply_id', array('type' => 'Integer'));

		$reportModel::belongsTo('reply', array('class' => $replyModel));
		$replyModel::hasMany('reports', array('class' => $reportModel));

		$userModel::getDataSource()->getAdapter()->createDataSchema($reportModel);


		$users = $userModel::find();

		$i = 0;
		$j = 0;
		$k = 0;
		$l = 0;
		$countReplies = 0;
		$tree = "";
		foreach($users->skip(1) AS $user)
		{
			$tree .= "-User:" . $user->id . PHP_EOL;
			if($i++ === 0)
			{
				$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" WHERE 1 LIMIT -1 OFFSET 1";
				$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
			}
			foreach($user->comments AS $comment)
			{
				$tree .= "--Comment:" . $comment->id . PHP_EOL;
				if($j++ === 0)
				{
					$sql = "SELECT \"comments\".\"id\" AS \"id\", \"comments\".\"user_id\" AS \"user_id\" FROM \"comments\" WHERE (\"comments\".\"user_id\" IN (2, 3, 4, 5, 6, 7))";
					$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
				}
				foreach($comment->replies->filter($replyModel::id()->in(3,5)) AS $reply)
				{
					$tree .= "---Reply:" . $reply->id . PHP_EOL;
					$countReplies++;
					if($k++ === 0)
					{
						$sql = "SELECT \"replys\".\"id\" AS \"id\", \"replys\".\"comment_id\" AS \"comment_id\" FROM \"replys\" WHERE (\"replys\".\"comment_id\" IN (2, 3, 4, 5, 109, 110))";
						$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
					}
					foreach($reply->reports AS $report)
					{
						$tree .= "----Report:" . $report->id . PHP_EOL;
					}
					if($l++ === 0)
					{
						$sql = "SELECT \"reports\".\"id\" AS \"id\", \"reports\".\"reply_id\" AS \"reply_id\" FROM \"reports\" WHERE (\"reports\".\"reply_id\" IN (3, 4, 5))";
						$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
					}
				}

			}
		}
		$this->assertSame(2, $countReplies);

		$assertedTree = '';
		$assertedTree .= '-User:2'        . PHP_EOL;
		$assertedTree .= '--Comment:109'  . PHP_EOL;
		$assertedTree .= '---Reply:3'     . PHP_EOL;
		$assertedTree .= '-User:3'        . PHP_EOL;
		$assertedTree .= '--Comment:2'    . PHP_EOL;
		$assertedTree .= '--Comment:110'  . PHP_EOL;
		$assertedTree .= '---Reply:5'     . PHP_EOL;
		$assertedTree .= '-User:4'        . PHP_EOL;
		$assertedTree .= '-User:5'        . PHP_EOL;
		$assertedTree .= '-User:6'        . PHP_EOL;
		$assertedTree .= '-User:7'        . PHP_EOL;
		$assertedTree .= '--Comment:3'    . PHP_EOL;
		$assertedTree .= '--Comment:4'    . PHP_EOL;
		$assertedTree .= '--Comment:5'    . PHP_EOL;

		$this->assertSame($assertedTree, $tree);

		$users = $userModel::find()->filter($userModel::id()->lt($userModel::age()));
		count($users);
		$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" WHERE (\"users\".\"id\" < \"users\".\"age\")";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());




		$comment = $commentModel::find()->first();
		$oldUser = $comment->user;
		$oldId = $comment->user_id;

		$comment->user_id = ($oldId+1);
		$this->assertNotSame($oldUser, $comment->user);
		$newUser = $userModel::find()->filter($userModel::id()->eq($oldId+1))->one();

		$this->assertSame($newUser, $comment->user);

		$comment->user_id = 11011;
		$this->assertSame(NULL, $comment->user);

		$sql = "SELECT \"users\".\"id\" AS \"id\", \"users\".\"nickname\" AS \"nickname\", \"users\".\"age\" AS \"age\" FROM \"users\" WHERE (\"users\".\"id\" = 11011)";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

		/*
		-------------------------------------------
		*/

		$reportModel::getDataSource()->getAdapter()->execute("INSERT INTO reports (reply_id, text) VALUES (1, 'Bist du Proletarier? Oder Prolet, Arier?')");
		$reportModel::getDataSource()->getAdapter()->execute("INSERT INTO reports (reply_id, text) VALUES (2, 'Warum muss alles gute so falsch sein? Warum ist alles schöne so schlecht?')");
		$reportModel::getDataSource()->getAdapter()->execute("INSERT INTO reports (reply_id, text) VALUES (3, 'Wieso gibt es auf der Welt keine Liebe?')");
		$reportModel::getDataSource()->getAdapter()->execute("INSERT INTO reports (reply_id, text) VALUES (2, 'Und weswegen hab ich eigentlich immer recht?')");

		$reports = $reportModel::find()->all();

		$i = 0;
		ob_start();
		foreach($reports AS $report)
		{
			if(++$i===0)
			{
				$sql = "SELECT \"reports\".\"id\" AS \"id\", \"reports\".\"reply_id\" AS \"reply_id\", FROM \"reports\" WHERE (\"reports\".\"id\" IN (1, 2, 3, 4))";
				$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
			}
			print 'Report:' . PHP_EOL;
			print 'id:' . $report->id . PHP_EOL;
			print 'text:' . $report->text . PHP_EOL;
		}
		ob_end_clean();

		$sql = "SELECT \"reports\".\"id\" AS \"id\", \"reports\".\"text\" AS \"text\" FROM \"reports\" WHERE (\"reports\".\"id\" IN (1, 2, 3, 4))";
		$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());



		/*
		------------------------------------------
		*/

		$dataSource = $reportModel::getDataSource();
		$recordsWithoutId = $dataSource->read($reports->all(array('fields' => array('text')))->getQuery());
		$this->assertTrue($recordsWithoutId[0]->isReadonly());
		try {
		$recordsWithoutId[0]->text = 'asd';
		} catch (\Dataphant\States\Exceptions\ImmutableException $e)
		{
			$immutable = TRUE;
		}
		$this->assertTrue($immutable);

		/*
		------------------------------------------
		*/

		$reports = $reportModel::find();
		$this->assertSame(4, $reports->calculate($reportModel::text()->count()));


		/*
		------------------------------------------
		*/

		$gameModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$gameModel::setEntityName('Game');
		$gameModel::defineProperty('user_id', array('type' => 'Integer'));

		$gameModel::belongsTo('user', array('class' => $userModel));
		$userModel::hasMany('games', array('class' => $gameModel));

		$userModel::getDataSource()->getAdapter()->createDataSchema($gameModel);

		$users = $userModel::find();

		foreach($users AS $user)
		{
			$user->games->calculate($gameModel::id()->max());
			$sql = "SELECT MAX(\"games\".\"id\") AS \"maximum_id\", \"games\".\"user_id\" AS \"user_id\" FROM \"games\" WHERE (\"games\".\"user_id\" IN (1, 2, 3, 4, 5, 6, 7)) GROUP BY (\"games\".\"user_id\")";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

			$user->games->calculate($gameModel::id()->count());
			$sql = "SELECT COUNT(\"games\".\"id\") AS \"count_id\", \"games\".\"user_id\" AS \"user_id\" FROM \"games\" WHERE (\"games\".\"user_id\" IN (1, 2, 3, 4, 5, 6, 7)) GROUP BY (\"games\".\"user_id\")";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

			$user->games->calculate($gameModel::id()->min());
			$sql = "SELECT MIN(\"games\".\"id\") AS \"minimum_id\", \"games\".\"user_id\" AS \"user_id\" FROM \"games\" WHERE (\"games\".\"user_id\" IN (1, 2, 3, 4, 5, 6, 7)) GROUP BY (\"games\".\"user_id\")";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

			$user->games->calculate($gameModel::id()->avg());
			$sql = "SELECT AVG(\"games\".\"id\") AS \"average_id\", \"games\".\"user_id\" AS \"user_id\" FROM \"games\" WHERE (\"games\".\"user_id\" IN (1, 2, 3, 4, 5, 6, 7)) GROUP BY (\"games\".\"user_id\")";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

			$user->games->calculate($gameModel::id()->sum());
			$sql = "SELECT SUM(\"games\".\"id\") AS \"sum_id\", \"games\".\"user_id\" AS \"user_id\" FROM \"games\" WHERE (\"games\".\"user_id\" IN (1, 2, 3, 4, 5, 6, 7)) GROUP BY (\"games\".\"user_id\")";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());

			break;
		}

		foreach($users AS $user)
		{
			$user->games->filter($gameModel::id()->lt(100))->calculate($gameModel::id()->count());
			$sql = "SELECT COUNT(\"games\".\"id\") AS \"count_id\" FROM \"games\" WHERE ((\"games\".\"user_id\" = " . $user->id . ") AND (\"games\".\"id\" < 100))";
			$this->assertSame($sql, $userModel::getDataSource()->getAdapter()->getLastStatement());
		}

	}


	public function testSingleTableInheritance()
	{
		$movieModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$movieModel::setEntityName('Movie');

		$productModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$productModel::setEntityName('Product');

		$reviewModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$reviewModel::setEntityName('Review');
		$reviewModel::setDiscriminator('class');
		$reviewModel::defineProperty('reviewable_id', array('type' => 'Integer'));


		$productReviewModel = $this->getMockClass($reviewModel, array(uniqid('method')));

		$productReviewModel::belongsTo('reviewable', array('class' => $productModel));
		$productModel::hasMany('reviews', array('class' => $productReviewModel, 'key_prefix' => 'reviewable'));

		$movieReviewModel = $this->getMockClass($reviewModel, array(uniqid('method')));
		$movieReviewModel::belongsTo('reviewable', array('class' => $movieModel));
		$movieModel::hasMany('reviews', array('class' => $movieReviewModel, 'key_prefix' => 'reviewable'));

		$reviewModel::getDataSource()->getAdapter()->createDataSchema($movieReviewModel);
		$reviewModel::getDataSource()->getAdapter()->createDataSchema($movieModel);
		$reviewModel::getDataSource()->getAdapter()->createDataSchema($productModel);

		$reviewModel::getDataSource()->getAdapter()->execute("INSERT INTO \"reviews\" (\"class\", reviewable_id) VALUES ('" . $movieReviewModel . "', 1)");
		$reviewModel::getDataSource()->getAdapter()->execute("INSERT INTO \"reviews\" (\"class\", reviewable_id) VALUES ('" . $movieReviewModel . "', 1)");
		$reviewModel::getDataSource()->getAdapter()->execute("INSERT INTO \"reviews\" (\"class\", reviewable_id) VALUES ('" . $movieReviewModel . "', 1)");
		$reviewModel::getDataSource()->getAdapter()->execute("INSERT INTO \"reviews\" (\"class\", reviewable_id) VALUES ('" . $movieReviewModel . "', 1)");
		$reviewModel::getDataSource()->getAdapter()->execute("INSERT INTO \"reviews\" (\"class\", reviewable_id) VALUES ('" . $movieReviewModel . "', 1)");


		$movie = $movieModel::build();
		$movie->save();

		$reviews = $movie->reviews;

		$this->assertSame(5, count($reviews));

		foreach($reviews AS $review)
		{
			$this->assertSame($movieReviewModel, get_class($review));
		}

		$product = $productModel::build();
		$product->save();

		try
		{
			$product->reviews[] = $movie->reviews[0];
		}
		catch(\Exception $e)
		{
			$msg = $e->getMessage();
		}

		$assertMsg = 'The record(' . $movieReviewModel . ', entityName:Review) has to belong to the same model as the collection(' . $productReviewModel . ', entityName:Review).';
		$this->assertSame($assertMsg, $msg);


		$simpleReview = $reviewModel::build(array('class' => $movieReviewModel));
		$this->assertSame($reviewModel, get_class($simpleReview));

	}


	public function testOneToOneRelationships()
	{
		$personModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$personModel::setEntityName('Person');

		$passportModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$passportModel::setEntityName('Passport');
		$passportModel::defineProperty('person_id', array('type' => 'Integer'));

		$personModel::hasOne('passport', array('class' => $passportModel));
		$passportModel::belongsTo('person', array('class' => $personModel));

		$personModel::getDataSource()->getAdapter()->createDataSchema($personModel);
		$passportModel::getDataSource()->getAdapter()->createDataSchema($passportModel);

		$person = $personModel::build();
		$passport = $passportModel::build();

		$person->passport = $passport;
		$this->assertSame($passport, $person->passport);
		$this->assertSame($passport->person, $person);

		$otherPassport = $passportModel::build();
		$otherPassport->person = $person;
		$this->assertSame($otherPassport->person, $person);

		// is it possible to refresh the other end of the relationship here?
		// $this->assertSame($otherPassport, $person->passport);
	}

	public function testManyToManyRelationships()
	{
		$teamModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$teamModel::defineProperty('name');
		$teamModel::setEntityName('Team');

		define('DBUG', $teamModel);

		$playerModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$playerModel::setEntityName('Player');

		$membershipModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$membershipModel::setEntityName('Membership');
		$membershipModel::defineProperty('player_id', array('type' => 'Integer'));
		$membershipModel::defineProperty('team_id', array('type' => 'Integer'));


		$membershipModel::belongsTo('player', array('class' => $playerModel));
		$membershipModel::belongsTo('team', array('class' => $teamModel));
		$teamModel::hasMany('memberships', array('class' => $membershipModel));
		$playerModel::hasMany('memberships', array('class' => $membershipModel));

		$teamModel::hasAndBelongsToMany('players', array('through' => 'memberships', 'class' => $playerModel));
		$playerModel::hasAndBelongsToMany('teams', array('through' => 'memberships', 'class' => $teamModel));

		$teamModel::getDataSource()->getAdapter()->createDataSchema($teamModel);
		$membershipModel::getDataSource()->getAdapter()->createDataSchema($membershipModel);
		$playerModel::getDataSource()->getAdapter()->createDataSchema($playerModel);

		$schiri = $playerModel::build();
		$schiri->save();

		$teams = $schiri->teams;

		$teams[] = array('name' => 'Team1');
		$teams[] = array('name' => 'Team2');
		$teams[] = array('name' => 'Team3');
		$teams[] = array('name' => 'Team4');
		$teams[] = array('name' => 'Team5');
		$teams[] = array('name' => 'Team6');


		$this->assertSame(6, count($teams));

		$teams->save();

		$sql = "INSERT INTO \"memberships\" (\"player_id\", \"team_id\") VALUES (1, 6)";
		$this->assertSame($sql, $teamModel::getDataSource()->getAdapter()->getLastStatement());

		$this->assertSame(6, count($teams));

		$r = $teams[0];
		$teams->removeRecord($r);

		$this->assertSame(5, count($teams));

		$teams->save();

		$this->assertSame(5, count($teams));

		$sql = "DELETE FROM \"memberships\" WHERE ((\"memberships\".\"id\" = 1))";
		$this->assertSame($sql, $teamModel::getDataSource()->getAdapter()->getLastStatement());

		$oldTeam = $teams[0];
		$teams->removeRecord($oldTeam);

		$this->assertSame(4, count($teams));

		$schiri->save();

		$sql = "DELETE FROM \"memberships\" WHERE ((\"memberships\".\"id\" = 2))";
		$this->assertSame($sql, $teamModel::getDataSource()->getAdapter()->getLastStatement());

		$this->assertSame(4, count($schiri->memberships));
		$this->assertSame(1, count($schiri->teams[2]->memberships));
		$this->assertSame(0, count($oldTeam->memberships));
	}

	public function testManyToManySmartEagerLoading()
	{
		$agencyModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$agencyModel::defineProperty('name');
		$agencyModel::setEntityName('Agency');

		$employeeModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$employeeModel::setEntityName('Employee');
		$employeeModel::defineProperty('agent_id', array('type' => 'Integer'));
		$employeeModel::defineProperty('agency_id', array('type' => 'Integer'));


		$agentModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$agentModel::defineProperty('codename');
		$agentModel::setEntityName('Agent');

		$agencyModel::hasMany('employees', array('class' => $employeeModel));
		$agentModel::hasMany('employments', array('class' => $employeeModel));
		$employeeModel::belongsTo('agent', array('class' => $agentModel));
		$employeeModel::belongsTo('agency', array('class' => $agencyModel));

		$agencyModel::hasAndBelongsToMany('agents', array('class' => $agentModel, 'through' => 'employees'));
		$agentModel::hasAndBelongsToMany('agencies', array('class' => $agencyModel, 'through' => 'employments', 'via' => 'agency'));

		$agentModel::getDataSource()->getAdapter()->createDataSchema($agentModel);
		$employeeModel::getDataSource()->getAdapter()->createDataSchema($employeeModel);
		$agencyModel::getDataSource()->getAdapter()->createDataSchema($agencyModel);

		$a = $agencyModel::getDataSource()->getAdapter();

		$a->execute('INSERT INTO agencys (name) VALUES (\'CIA\')'); #1
		$a->execute('INSERT INTO agencys (name) VALUES (\'NSA\')'); #2
		$a->execute('INSERT INTO agencys (name) VALUES (\'CSP\')'); #3
		$a->execute('INSERT INTO agencys (name) VALUES (\'MI6\')'); #4

		$a->execute('INSERT INTO agents (codename) VALUES (\'James\')'); #1
		$a->execute('INSERT INTO agents (codename) VALUES (\'Sam\')'); #2
		$a->execute('INSERT INTO agents (codename) VALUES (\'MrX\')'); #3
		$a->execute('INSERT INTO agents (codename) VALUES (\'SCHIRI\')'); #4
		$a->execute('INSERT INTO agents (codename) VALUES (\'Palle\')'); #5
		$a->execute('INSERT INTO agents (codename) VALUES (\'RaPiD\')'); #6
		$a->execute('INSERT INTO agents (codename) VALUES (\'Fox\')'); #7

		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (1, 4)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (1, 3)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (2, 2)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (2, 5)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (3, 4)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (3, 5)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (3, 6)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (4, 6)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (4, 1)');
		$a->execute('INSERT INTO employees (agency_id, agent_id) VALUES (4, 3)');

		$agencies = $agencyModel::find()->all();

		$tree = '';
		$k = 0;
		$i = 0;
		foreach($agencies AS $ag)
		{
			$sql = 'SELECT "agencys"."id" AS "id", "agencys"."name" AS "name" FROM "agencys" WHERE 1';
			if($k++ === 0) $this->assertSame($sql, $agencyModel::getDataSource()->getAdapter()->getLastStatement());

			$tree .= $ag->name . PHP_EOL;
			foreach($ag->agents AS $agent)
			{
				$sql = 'SELECT "agents"."id" AS "id", "agents"."codename" AS "codename" FROM "agents" WHERE ("agents"."id" IN (4, 3, 2, 5, 6, 1))';
				if($i++ === 0) $this->assertSame($sql, $agencyModel::getDataSource()->getAdapter()->getLastStatement());

				$tree .= "-" . $agent->id . ':' . $agent->codename . '('.count($agent->agencies).')' . PHP_EOL;

			}
		}

		$assertedTree = '';
		$assertedTree .= "CIA"          . PHP_EOL;
		$assertedTree .= "-4:SCHIRI(2)" . PHP_EOL;
		$assertedTree .= "-3:MrX(2)"    . PHP_EOL;
		$assertedTree .= "NSA"          . PHP_EOL;
		$assertedTree .= "-2:Sam(1)"    . PHP_EOL;
		$assertedTree .= "-5:Palle(2)"  . PHP_EOL;
		$assertedTree .= "CSP"          . PHP_EOL;
		$assertedTree .= "-4:SCHIRI(2)" . PHP_EOL;
		$assertedTree .= "-5:Palle(2)"  . PHP_EOL;
		$assertedTree .= "-6:RaPiD(2)"  . PHP_EOL;
		$assertedTree .= "MI6"          . PHP_EOL;
		$assertedTree .= "-6:RaPiD(2)"  . PHP_EOL;
		$assertedTree .= "-1:James(1)"  . PHP_EOL;
		$assertedTree .= "-3:MrX(2)"    . PHP_EOL;

		$this->assertSame($assertedTree, $tree);
	}

	public function testUnmappedProperties()
	{
		$categoryModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$categoryModel::setEntityName('Category');


		// TODO: choose a syntax for defining setter and getter for unmapped properties
	}


	public function testLazyPropertiesCanBeEagerLoadedWithEagerLoadingCollection()
	{
		$parentModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$parentModel::defineProperty('name');
		$parentModel::setEntityName('Parent');

		$childModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$childModel::setEntityName('Child');
		$childModel::defineProperty('description', array('type' => 'Text', 'lazy' => TRUE));
		$childModel::defineProperty('parent_id', array('type' => 'Integer'));


		$parentModel::hasMany('childs', array('class' => $childModel));
		$childModel::belongsTo('parent', array('class' => $parentModel));

		$parentModel::getDataSource()->getAdapter()->createDataSchema($parentModel);
		$childModel::getDataSource()->getAdapter()->createDataSchema($childModel);

		$a = $parentModel::getDataSource()->getAdapter();
		$a->execute('INSERT INTO parents (name) VALUES (\'Homer\')'); #1
		$a->execute('INSERT INTO parents (name) VALUES (\'March\')'); #2

		$parents = $parentModel::find();

		foreach($parents AS $parent)
		{
			foreach($parent->childs->eagerLoad(array('description')) AS $child)
			{
				# do nothing :P
			}
		}

		$sql = 'SELECT "childs"."id" AS "id", "childs"."parent_id" AS "parent_id", "childs"."description" AS "description" FROM "childs" WHERE ("childs"."parent_id" IN (1, 2))';
		$this->assertSame($sql, $parentModel::getDataSource()->getAdapter()->getLastStatement());
	}
}
