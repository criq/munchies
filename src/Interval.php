<?php

namespace Fatty;

class Interval
{
	protected $min;
	protected $max;

	public function __construct($min, $max)
	{
		$this->min = $min;
		$this->max = $max;
	}

	public function getMin()
	{
		return $this->min;
	}

	public function getMax()
	{
		return $this->max;
	}
}
