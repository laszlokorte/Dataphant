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

use Exception;

class ManyToOneRelationship extends RelationshipBase
{

	public function __construct($name, $sourceModel, $targetModel, $options = array())
	{
		parent::__construct($name, $sourceModel, $targetModel, $options);

		$this->getSourceKeys();
	}

	public function getSourceKeys()
	{
		if( ! isset($this->sourceKeys))
		{
			$this->sourceKeys = array();

			$sourceModel = $this->getSourceModel();
			$name = $this->getName();

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
				$name = $inflector->underscore($name);
			}
			else
			{
				$name = $this->options['key_prefix'];
			}

			if(isset($this->options['keys']))
			{
				foreach($this->options['keys'] AS $keyName)
				{
					$propertyName = $name . '_' . $keyName;
					$this->sourceKeys[$propertyName] = $sourceModel::getProperty($propertyName);
				}
			}
			else
			{
				// Try to guess the Foreign keys by their name.

				$properties = $sourceModel::getProperties();
				foreach($properties AS $property)
				{
					$propertyPrefix = $name . '_';
					$prefixLength = strlen($propertyPrefix);
					$propertyName = $property->getName();

					if(substr($propertyName, 0, $prefixLength) === $propertyPrefix)
					{
						$this->sourceKeys[$propertyName] = $property;
					}
				}
				if(count($this->sourceKeys) < 1)
				{
					throw new Exception("No SourceKeys for the relationship {$this->name} of the {$this->sourceModel} model could be found.");
				}
			}
		}

		return $this->sourceKeys;
	}

	public function setValueFor($source, $target)
	{
		# TODO: inverse set
		# problems:
		# -recursion
		# -we dont know all sub collections of the inverse relationships collection
		#  so we cant change them
		#
		# special case:
		# if the reverse relationship is a OneToOne there should not be more
		# than on collection so we could make the inverse set
		#
		$target = $this->typecast($target);
		$this->setValueForInternal($source, $target);
		$targetKeys = $this->getTargetKeys();
		$sourceKeys = $this->getSourceKeys();
		$keyCount = count($targetKeys);

		foreach($targetKeys AS $name => $targetKey)
		{
			$currentSourceKey = current($sourceKeys);
			$name = $currentSourceKey->getName();
			if($target !== NULL)
			{
				$source->$name = $targetKey->getValueFor($target);
			}
			else
			{
				$source->$name = NULL;
			}
			next($sourceKeys);
		}
	}

	protected function typecast($target)
	{
		if(is_array($target))
		{
			$model = $this->getTargetModel();
			return $model::build($target);
		}
		else
		{
			return $target;
		}
	}

	public function getValueFor($source)
	{
		$this->directLoadFor($source);
		$this->lazyLoadFor($source);

		#HACK?
		# to be consistent with the other relationships
		# we should use the getCollectionFor method
		#
		# In fact the getValueFor method itself just returns
		# ONE record, not a collection.
		#
		# Event internaly not the collection but just the record is saved
		#
		# using the getCollectionFor method would:
		# 1. take the already loaded record
		# 2. create a collection for this record
		# 3. return the created collection
		#
		# We would then jus take the first record of the collection
		# so the collection would just be an overhead
		#
		#
		# $collection = $this->getCollectionFor($source);

		$record = $this->getValueForInternal($source);

		if($record !== NULL)
		{
			# $record = $collection->first();
			if($this->keysStillValidFor($source, $record))
			{
				return $record;
			}
			else
			{
				$this->unloadForInternal($source);
				return $this->getValueFor($source);
			}
		}
	}

	protected function keysStillValidFor($source, $target)
	{
		if($source->isNew() || $target->isNew())
		{
			return TRUE;
		}
		$targetKeys = $this->getTargetKeys();
		$sourceKeys = $this->getSourceKeys();

		foreach($targetKeys AS $name => $targetKey)
		{
			$currentSourceKey = current($sourceKeys);

			if($currentSourceKey->getValueFor($source) !== $targetKey->getValueFor($target))
			{
				return FALSE;
			}

			next($sourceKeys);
		}

		return TRUE;
	}

	public function getCollectionFor($source)
	{
		$record = $this->getValueForInternal($source);

		if($record !== NULL)
		{
			return $record->getCollectionForSelf();
		}
	}


	protected function directLoadFor($source)
	{
		if($this->isLoadedFor($source))
		{
			return;
		}

		$model = $this->getTargetModel();
		$sourceKeys = $this->getSourceKeys();

		$targetKeyValues = array();

		foreach($sourceKeys AS $sourceKey)
		{
			$targetKeyValues[] = $sourceKey->getValueFor($source);
		}

		$target = $model::find()->get($targetKeyValues);

		if($target !== NULL)
		{
			$this->eagerLoadTargets($source, array($target));
		}
	}


	public function lazyLoadFor($record)
	{
		if($this->isLoadedFor($record))
		{
			return;
		}

		if( ! $record->isNew())
		{
			$this->eagerLoadFor($record->getCollection());
		}

	}


	protected function eagerLoadTargets($source, $targets)
	{
		$first = isset($targets[0]) ? $targets[0] : NULL;
		$this->setValueFor($source, $first);
	}

}
