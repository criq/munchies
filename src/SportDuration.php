<?php

namespace Fatty;

abstract class SportDuration extends AmountWithUnit
{
	const QUOTIENT = null;

	public function __construct(Amount $amount, string $unit = 'minutesPerWeek')
	{
		return parent::__construct($amount, $unit);
	}

	public function getActivityAmount()
	{
		return new Activity($this->getAmount()->getValue() * .001 * static::QUOTIENT);
	}
}
