<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Relationships;

use Dataphant\Utils\Inflector;

use Dataphant\Exceptions\UndefinedRelationshipException;

class ManyToManyRelationship extends OneToManyRelationship
{

	/**
	 * The links to resolve the relationship
	 *
	 * @var array
	 */
	protected $links;


	/**
	 * The relationship between JoinModel and TargetModel
	 *
	 * @var RelationshipInterface
	 */
	protected $via;


	/**
	 * The relationship between SourceModel and JoinModel
	 *
	 * @var RelationshipInterface
	 */
	protected $through;

	public function getNewQueryFor($source, $otherQuery = NULL)
	{
		$query = parent::getNewQueryFor($source, $otherQuery);
		$query['links'] = $this->getLinks();

		return $query;
	}


	public function getTargetKeys()
	{
		if( ! isset($this->targetKeys))
		{
			$targetModel = $this->getTargetModel();
			$this->targetKeys = $targetModel::getKeys();
		}

		return $this->targetKeys;
	}


	protected function getSourceScope($source)
	{
		# TODO:
		/*
			Create an EqualToComparison between the inverse relationship and the source.
			The Query class should only be accessed by the Adapter.
		*/
		$q = new \Dataphant\Query\Comparisons\EqualToComparison($this->getThrough(), $source);

		return $q;
	}


	public function eagerLoadFor($collection, $otherQuery = NULL)
	{
		$through = $this->getThrough();
		$via = $this->getVia();

		$intermediaries = $through->eagerLoadFor($collection);
		$targets = $via->eagerLoadFor($intermediaries, $otherQuery);

		$this->associateTargets($collection, $targets);

		return $targets;
	}


	protected function associateTargets($sources, $targets)
	{
		$through = $this->getThrough();
		$via = $this->getVia();

		$sources->setChildCollectionFor($this, $targets);

		foreach($sources AS $source)
		{
			$targets = array();

			foreach($through->getValueFor($source) AS $inter)
			{
				$targets[] = $via->getValueFor($inter);
			}

			$this->eagerLoadTargets($source, $targets);
		}
	}


	/**
	 * Returns the relationship between SourceModel and JoinModel.
	 *
	 * Imagine the following:
	 * SourceModel <-RelA-> JoinModel <-RelB-> TargetModel
	 * or
	 * UserModel <-1:n-> MembershipModel <-n:1-> TeamModel
	 *
	 * This method returns RelA
	 *
	 * @return void
	 */
	public function getThrough()
	{
		if( ! isset($this->through))
		{
			$through = $this->options['through'];

			if($through instanceof RelationshipInterface)
			{
				$this->through = $through;
			}
			else
			{
				$model = $this->getSourceModel();
				$relationships = $model::getRelationships();
				$name = $through;

				$this->through = $relationships[$name];
			}

			$this->through->getTargetKeys();
		}

		return $this->through;
	}


	/**
	 * Returns the relationship between JoinModel and TargetModel.
	 *
	 * Imagine the following:
	 * SourceModel <-RelA-> JoinModel <-RelB-> TargetModel
	 * or
	 * UserModel <-1:n-> MembershipModel <-n:1-> TeamModel
	 *
	 * This method returns RelB
	 *
	 * @return void
	 */
	public function getVia()
	{
		if( ! isset($this->via))
		{
			$via = isset($this->options['via']) ? $this->options['via'] : NULL;

			if($via instanceof RelationshipInterface)
			{
				$this->via = $via;
			}
			else
			{
				$through = $this->getThrough();
				$throughModel = $through->getTargetModel();
				$relationships = $throughModel::getRelationships();

				if($via !== NULL)
				{
					if(isset($relationships[$via]))
					{
						$this->via = $relationships[$via];
					}
					else
					{
						throw new UndefinedRelationshipException("No relationship named {$via} in {$throughModel}.");
					}
				}
				else
				{
					$name = $this->getName();

					# TODO: use globaly configured inflector
					$inflector = Inflector::getInstance();
					$singularName = $inflector->singularize($name);

					if(isset($relationships[$name]))
					{
						$this->via = $relationships[$name];
					}
					elseif(isset($relationships[$singularName]))
					{
						$this->via = $relationships[$singularName];
					}
					else
					{
						throw new UndefinedRelationshipException("No relationship named {$name} or {$singularName} in {$throughModel}.");
					}
				}

			}

			$this->via->getTargetKeys();
		}

		return $this->via;
	}

	/**
	 * Get a list of the all the relationships this relationship goes throught.
	 * Eg:
	 * User has many memberships
	 * Team has many memberships
	 *
	 * User has many team through memberships
	 *
	 * This method would return an array containing
	 * the User-Membership-Relationship and the Membership-Team-Realtionship
	 *
	 * In SQL you would say this method returns the list of joins needed to be made.
	 *
	 * @return void
	 */
	public function getLinks()
	{
		if( ! isset($this->links))
		{

			$links = array();
			$relationships = array($this->getVia(), $this->getThrough());

			while($relationship = array_shift($relationships))
			{
				if($relationship instanceof ManyToManyRelationship)
				{
					$subLinks = $relationship->getLinks();
					foreach($subLinks AS $l)
					{
						array_unshift($relationships, $l);
					}
				}
				else
				{
					array_unshift($links, $relationship);
				}
			}

			$this->links = $links;

		}
		return $this->links;
	}

	protected function getCollectionClass()
	{
		return __NAMESPACE__ . '\\ManyToManyCollection';
	}


	public function isCrossDataSource()
	{
		$links = $this->getLinks();

		foreach($links AS $link)
		{
			if($link->isCrossDataSource())
			{
				return TRUE;
			}
		}

		return FALSE;
	}

}
