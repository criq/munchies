<?php

namespace Fatty;

use Katu\Tools\Calendar\Time;

class DateTimeWeight
{
	protected $time;
	protected $weight;

	public function __construct(Time $time, Weight $weight)
	{
		$this->time = $time;
		$this->weight = $weight;
	}

	public function getTime(): Time
	{
		return $this->time;
	}

	public function getWeight(): Weight
	{
		return $this->weight;
	}
}
