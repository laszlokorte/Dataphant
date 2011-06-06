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

use Dataphant\Relationships\OneToManyCollection;
use Dataphant\Query\Query;
use Dataphant\Utils\Inflector;

use Exception;

class OneToManyRelationship extends RelationshipBase
{

	public function __construct($name, $sourceModel, $targetModel, $options = array())
	{
		parent::__construct($name, $sourceModel, $targetModel, $options);

		$this->getSourceKeys();
	}

	public function getTargetKeys()
	{
		if( ! isset($this->targetKeys))
		{
			$this->targetKeys = array();

			$targetModel = $this->getTargetModel();
			$sourceModel = $this->getSourceModel();

			if( ! isset($this->options['inflector']))
			{
				# TODO: use globaly configured inflector
				$inflector = Inflector::getInstance();
			}
			else
			{
				$inflector = $this->options['inflector'];
			}

			if( ! isset($this->options['key_prefix']))
			{
				# TODO: use the Model's class name here
				# for the case the entity name has been changed.
				#
				# If an Article has many Comments but the comment's entity name has been changed to
				# com_45_ments the article's getter and setter for comments should not be com_45_ments.
				#
				$foreign_key_prefix = $inflector->underscore($sourceModel::getEntityName());
			}
			else
			{
				$foreign_key_prefix = $this->options['key_prefix'];
			}

			foreach($this->getSourceKeys() AS $key)
			{
				$propertyName = $foreign_key_prefix . '_' . $key->getName();

				$this->targetKeys[$propertyName] = $targetModel::getProperty($propertyName);
			}
		}

		return $this->targetKeys;
	}

	public function setValueFor($source, $targets)
	{
		$this->lazyLoadFor($source);

		$this->getCollectionFor($source)->replaceRecords($targets);
	}

	public function getValueFor($source)
	{
		$this->lazyLoadFor($source);

		$collection = $this->getCollectionFor($source);

		return $collection;
	}


	public function getCollectionFor($source)
	{
		$collection = $this->getValueForInternal($source);

		return $collection;
	}


	public function lazyLoadFor($record)
	{
		if( ! $this->isLoadedFor($record))
		{
			$this->setValueForInternal($record, $this->createCollectionFor($record));
		}
	}


	/**
	 * Set the $targets records to be associated with the $source record.
	 * This is a template method called by the associateTargets()
	 * method of the RelationshipBase class.
	 *
	 * @param string $source
	 * @param string $targets
	 *
	 * @return void
	 */
	protected function eagerLoadTargets($source, $targets)
	{
		$this->lazyLoadFor($source);
		$collection = $this->getCollectionFor($source);
		$collection->setRecords($targets);
		$this->setValueForInternal($source, $collection);
	}


	/**
	 * Create a collection object to contain the target records for the given $source
	 *
	 * @param RecordInterface $source
	 *
	 * @return OneToManyCollection
	 */
	protected function createCollectionFor($source)
	{
		$collectionClass = $this->getCollectionClass();

		$collection = new $collectionClass($this->getTargetModel());
		$collection->setRelationship($this);
		$collection->setSource($source);

		if($source->isNew())
		{
			$collection->replaceRecords(array());
		}

		return $collection;
	}


	protected function getCollectionClass()
	{
		return __NAMESPACE__ . '\\OneToManyCollection';
	}
}
