<?php

/*
 * This file is part of Dataphant.
 *
 * (c) Laszlo Korte <me@laszlokorte.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * and AUTHORS files that was distributed with this source code.
 */

namespace Dataphant\Utils;

/*
 * InflectorInterface
 */
interface InflectorInterface
{

	public function pluralize($string);

	public function singularize($string);

	public function camelize($string);

	public function underscore($string);

	public function humanize($string);

}
