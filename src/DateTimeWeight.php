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

	public function getDateTime(): \DateTime
	{
		return $this->dateTime;
	}

	public function getWeight(): Weight
	{
		return $this->weight;
	}
}
