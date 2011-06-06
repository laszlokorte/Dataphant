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
 * Logger
 */
class Logger implements LoggerInterface
{

	protected $labels = array(
		0 => 'INFO',
		100 => '',
	);

	protected $target;

	protected $level;

	protected $buffer = array();

	public function __construct($file, $level, $autoFlush = FALSE)
	{
		$this->target = fopen($file, 'a');
		$this->level = $level;
		$this->autoFlush = $autoFlush;
	}

	public function __destruct()
	{
		fclose($this->target);
	}

	public function setLevel($level)
	{
		$this->level = $level;
	}

	public function log($level, $msg)
	{
		if($level >= $this->level)
		{
			$label = $this->levelToLabel($level);
			$this->writeLine($msg);
		}
	}

	protected function levelToLabel($level)
	{
		return $this->labels[$level];
	}

	protected function getTime()
	{
		return date("D M j G:i:s T Y");
	}

	public function writeLine($line)
	{
		$this->buffer[] = $line;

		if($this->autoFlush === TRUE)
		{
			$this->flush();
		}
	}

	public function flush()
	{
		while($line = array_shift($this->buffer))
		{
			fwrite($this->target, $line . PHP_EOL);
		}
	}

	public function getBuffer()
	{
		return $this->buffer;
	}

}
