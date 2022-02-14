<?php

namespace Fatty;

class DateTimeWeight
{
	protected $dateTime;
	protected $weight;

	public function __construct(\DateTime $dateTime, Weight $weight)
	{
		$this->dateTime = $dateTime;
		$this->weight = $weight;
	}
}
