<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Query\Aggregators;

/*
 * AggregtorInterface
 */
interface AggregatorInterface
{

	public function getProperty();

	public function getValueFor($collection);

	public function getAliasName();

}
