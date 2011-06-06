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
 * LoggerInterface
 */
interface LoggerInterface
{
	const INFO = 0;
	const OFF = 100;

	public function setLevel($level);

	public function log($level, $message);

	public function writeLine($line);

	public function flush();

	public function getBuffer();

}
