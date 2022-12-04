<?php

namespace Fatty\Pregnancy;

use Katu\Tools\Calendar\Interval;

class Trimester
{
	protected $index;
	protected $interval;

	public function __construct(int $index, Interval $interval)
	{
		$this->index = $index;
		$this->interval = $interval;
	}
}