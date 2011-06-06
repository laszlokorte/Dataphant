<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant;

use Iterator;

/*
 * CollectionIterator
 */
class CollectionIterator implements Iterator
{

	protected $recordsOriginalCollection;

	protected $collection;

	protected $counter;


	public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function rewind()
    {
        $this->counter = 0;
    }


    public function current()
    {
		# The current record gets told which collection he belongs during the iteration.
		# This allows to eager load it's relationships' values for all the other records
		# of the same collection.
		$record = $this->collection[$this->counter];
		$this->recordsOriginalCollection = $record->getCollection(FALSE);
		$record->setCollection($this->collection);

        return $record;
    }

    public function key()
    {
        return $this->counter;
    }

    public function next()
    {
		# The record of the prvious iteration should not know anymore to wich
		# collection he belongs.
		$record = $this->collection[$this->counter];
		$record->setCollection($this->recordsOriginalCollection);

        $this->counter++;
    }

    public function valid()
    {
		$limit = count($this->collection);
		return $this->counter < $limit;
    }

	public function __destruct()
	{
		# After the last iteration the last record should not know anymore
		# which collection he belongs to.
		$index = $this->counter - 1;
		if($index > -1)
		{
			$record = $this->collection[$index];
			$record->setCollection($this->recordsOriginalCollection);
		}
	}

}
