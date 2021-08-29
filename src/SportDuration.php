<?php

namespace Fatty;

abstract class SportDuration extends AmountWithUnit
{
	const QUOTIENT = null;

	public function __construct($amount, $unit = 'minutesPerWeek')
	{
		return parent::__construct($amount, $unit);
	}

	public function getActivityAmount()
	{
		return new ActivityAmount($this->amount * .001 * static::QUOTIENT);
	}
}
