<?php

namespace Fatty\Pregnancy;

use Katu\Tools\Calendar\Interval;

class Trimester
{
	protected $index;
	protected $interval;

	public function __construct(int $index, Interval $interval)
	{
		$this->setIndex($index);
		$this->setInterval($interval);
	}

	public function setIndex(int $index): Trimester
	{
		$this->index = $index;

		return $this;
	}

	public function getIndex(): int
	{
		return $this->index;
	}

	public function setInterval(Interval $interval): Trimester
	{
		$this->interval = $interval;

		return $this;
	}

	public function getInterval(): Interval
	{
		return $this->interval;
	}
}
