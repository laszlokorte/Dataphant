<?php

namespace Dataphant\Tests\Query;

use Dataphant\Tests\BaseTestCase;

use Dataphant\Query\Path;

class PathTest extends BaseTestCase
{
	public function setUp()
	{
		$userModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$this->userNicknameProperty = $userModel::defineProperty('nickname');

		$squadModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));

		$commentModel = $this->getMockClass('Dataphant\\ModelBase', array(uniqid('method')));
		$commentModel::defineProperty('user_id', array('type' => 'Integer'));

		$this->commentUserRelationship = $commentModel::belongsTo('user', array('class' => $userModel));
		$this->userCommentRelationship = $userModel::hasMany('comments', array('class' => $commentModel));
		// $this->squadUserRelationship = $this->getMock('Dataphant\\Relationships\\ManyToManyRelationship', array(uniqid('method')), array('users', $this->squadModel, $this->userModel));
		$this->userSquadRelationship = $userModel::hasMany('squads', array('class' => $squadModel));

		$this->userModel = $userModel;
		$this->squadModel = $squadModel;
		$this->commentModel = $commentModel;

	}

	public function testListOfRelationshipsMustNotBeEmpty()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidRelationshipException');
		$path = new Path(array());
	}

	public function testRelationshipsCanBeSetOnInitialization()
	{
		$path = new Path(array($this->commentUserRelationship));

	}

	public function testLastRelationshipCanBePassed()
	{
		$relationships = array($this->commentUserRelationship, $this->userSquadRelationship);
		$path = new Path($relationships);
		$this->assertSame($this->userSquadRelationship, $path->getLastRelationship());
	}

	public function testPropertyCanBeSetOnInitialization()
	{
		$path = new Path(array($this->commentUserRelationship), 'nickname');
		$this->assertSame($this->userNicknameProperty, $path->getProperty());
	}

	public function testPropertyHaveToBelongToTheLastPassedRelationshipsTargetModel()
	{
		$this->setExpectedException('Dataphant\\Query\\Exceptions\\InvalidPropertyException');
		$path = new Path(array($this->commentUserRelationship), 'unknownProp');
	}

	public function testMethodCallsGetDelegatedToThePropertyIfSet()
	{
		$path = new Path(array($this->commentUserRelationship), 'nickname');
		$this->assertSame($this->userNicknameProperty->getName(), $path->getName());
	}

	public function testMethodCallFailsIfPropertyHasNoSuchMethod()
	{
		$this->setExpectedException('\\BadMethodCallException');
		$path = new Path(array($this->commentUserRelationship), 'nickname');
		$path->unknownMethod();
	}

	public function testMethodCallsMatchingTheLastRelationshipsTargetModelsRelationshipResultInNewPath()
	{
		$path = new Path(array($this->commentUserRelationship));
		$squadPath = $path->squads();

		$this->assertInstanceOf('\Dataphant\Query\PathInterface', $squadPath);
		$this->assertSame($this->userSquadRelationship, $squadPath->getLastRelationship());
		$this->assertNull($squadPath->getProperty());
	}

	public function testMethodCallsMatchingTheLastRelationshipsTargetModelsPropertyResultInNewPath()
	{
		$path = new Path(array($this->commentUserRelationship));
		$nicknameProperty = $path->nickname();

		$this->assertInstanceOf('\Dataphant\Query\PathInterface', $nicknameProperty);
		$this->assertSame($this->userNicknameProperty, $nicknameProperty->getProperty());
	}

	public function testMethodCallsNotMatchingAnythingResultInAnException()
	{
		$this->setExpectedException('BadMethodCallException');

		$path = new Path(array($this->commentUserRelationship));
		$nicknameProperty = $path->foobar();
	}

	public function testRelationshipsCanBeIterated()
	{
		$relationships = array($this->commentUserRelationship, $this->userSquadRelationship);
		$i = count($relationships);
		$path = new Path($relationships);
		foreach($path AS $key => $relationship)
		{
			$this->assertSame($relationships[$key], $relationship);
			$i--;
		}
		$this->assertSame(0, $i);
	}

	public function testOrderCanBeGeneratedWhenPropertyIsSet()
	{
		$path = new Path(array($this->commentUserRelationship), 'nickname');

		$order = $path->asc();
		$this->assertInstanceOf('\Dataphant\\Query\\OrderInterface', $order);

		$order = $path->desc();
		$this->assertInstanceOf('\Dataphant\\Query\\OrderInterface', $order);
	}


	public function testOperationCanBeGeneratedWhenPropertyIsSet()
	{
		$path = new Path(array($this->commentUserRelationship), 'nickname');

		$operator = $path->avg();
		$this->assertInstanceOf('\Dataphant\\Query\\Aggregators\\AverageAggregator', $operator);

		$operator = $path->count();
		$this->assertInstanceOf('\Dataphant\\Query\\Aggregators\\CountAggregator', $operator);

		$operator = $path->min();
		$this->assertInstanceOf('\Dataphant\\Query\\Aggregators\\MinimumAggregator', $operator);

		$operator = $path->max();
		$this->assertInstanceOf('\Dataphant\\Query\\Aggregators\\MaximumAggregator', $operator);

		$operator = $path->sum();
		$this->assertInstanceOf('\Dataphant\\Query\\Aggregators\\SumAggregator', $operator);
	}

	public function testComparisonCanBeGeneratedWhenPropertyIsSet()
	{
		$path = new Path(array($this->commentUserRelationship), 'nickname');
		$numValue = 23;
		$strValue = 'Elephant';

		$comparison = $path->eq($strValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);

		$comparison = $path->gt($numValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);

		$comparison = $path->gte($numValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);

		$comparison = $path->lt($numValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);

		$comparison = $path->lte($numValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);

		$comparison = $path->like($strValue);
		$this->assertInstanceOf('\Dataphant\\Query\\Comparisons\\ComparisonInterface', $comparison);
	}

}
