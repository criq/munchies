<?php

namespace Fatty;

class Energy extends AmountWithUnit
{
	const KCAL_TO_KJ_RATIO = 4.128;

	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Energy
	{
		switch (strtolower($this->getUnit())) {
			case 'kj':
				return clone $this;
				break;
			case 'kcal':
				return new static($this->getAmount()->getMultiplied(1 / static::KCAL_TO_KJ_RATIO), 'kCal');
				break;
		}
	}

	public function getInUnit(string $unit): Energy
	{
		switch (strtolower($unit)) {
			case 'kj':
				return $this->getInBaseUnit();
				break;
			case 'kcal':
				return new static($this->getAmount()->getMultiplied(static::KCAL_TO_KJ_RATIO), 'kCal');
				break;
		}
	}
}
