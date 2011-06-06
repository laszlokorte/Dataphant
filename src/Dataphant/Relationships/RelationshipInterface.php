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

use Dataphant\ComparableInterface;

/**
 * A Relationship represents one direction of a relationship between two models.
 * It has one source and one target model.
 * When two models are related to each other there should be two relationship instances
 * One for each direction
 */
interface RelationshipInterface extends ComparableInterface
{
	/**
	 * The relationships name.
	 * When a User is defined to have many comments, the relationshipss name would be 'comments'.
	 *
	 * This is also the name of the getter and setter method of the source model's instance
	 * eg $user->comments = ...
	 * or $user->comments
	 *
	 * @return String
	 */
	public function getName();


	/**
	 * The source model's class name.
	 *
	 * eg Comment
	 *
	 * @return string The class name.
	 */
	public function getSourceModel();

	/**
	 * Get the Key properties of the relationships source model.
	 *
	 * When the source model is the User class and it has just one key property named id,
	 * This method returns and array containing just this one property object.
	 *
	 * The source's key properties are used to identity the source model's records in the relationship.
	 *
	 * @return array The list of key properties.
	 */
	public function getSourceKeys();


	/**
	 * The target models class name.
	 *
	 * eg User
	 *
	 * @return string The class name.
	 */
	public function getTargetModel();


	/**
	 * Get the Key properties of the relationships target model.
	 *
	 * When the target model is the Comment class and it has just one key property named id,
	 * This method returns and array containing just this one property object.
	 *
	 * The target's key properties are used to identity the target model's records in the relationship.
	 *
	 *
	 * @return array The list of key properties.
	 */
	public function getTargetKeys();


	/**
	 * Get the additional options set for the relationship.
	 *
	 * @return array The options Array.
	 */
	public function getOptions();


	/**
	 * Get the association's value for the given record.
	 *
	 * @return mixed
	 */
	public function getValueFor($record);


	/**
	 * Set the relationships value for the given $sourceRecord to the given $target.
	 *
	 * @param RecordInterface $sourceRecord
	 * @param mixed $targetRecord
	 *
	 * @return void
	 */
	public function setValueFor($sourceRecord, $target);


	/**
	 * Checks if the association is already loaded for the given $sourceRecord.
	 *
	 * @return boolean
	 */
	public function isLoadedFor($sourceRecord);


	/**
	 * Loads the association for the given sourceRecord.
	 *
	 * @return void
	 */
	public function lazyLoadFor($sourceRecord);


	/**
	 * Load the association for all the records of the given collection.
	 *
	 * @param string $collection
	 * @param QueryInterface $otherQuery
	 *
	 * @return void
	 */
	public function eagerLoadFor($collection, $otherQuery = NULL);


	/**
	 * Check if the given value is valid for the relationship.
	 *
	 * @param mixed $value
	 *
	 * @return boolean
	 */
	public function isValidValue($value);


	/**
	 * Get the collection object containing the $targets of the $source.
	 *
	 * @param RecordInterface $source
	 *
	 * @return CollectionInterface
	 */
	public function getCollectionFor($source);



	public function getNewQueryFor($source, $otherQuery = NULL);


	/**
	 * The relationships counter part.
	 * In case of a user having multiple comments this would return the relationship of one comment belongs to one user
	 *
	 * @return RelationshipInterface
	 */
	public function getInverse();


	/**
	 * Set the relationships counter part.
	 *
	 * @return void
	 */
	public function setInverse($relationship);


	/**
	 * Check if the property can be set bia "mass assignment"
	 * Mass assignment:
	 * Not setting a single property per time but an array of properties.
	 * eg:
	 * $user->setAttributes($_POST);
	 * instead of
	 * $user->nickname = $_POST['nickname'];
	 * $user->password = $_POST['password'];
	 * $user->email = $_POST['email'];
	 *
	 * @return boolean
	 */
	public function isMassAssignable();



	public function isCrossDataSource();
}
