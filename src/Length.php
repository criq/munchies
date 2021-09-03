<?php

namespace Fatty;

class Length extends AmountWithUnit
{
	const BASE_UNIT = 'm';

	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Length
	{
		switch (mb_strtolower($this->getUnit())) {
			case mb_strtolower(static::getBaseUnit()):
				return clone $this;
				break;
			case 'cm':
				return new static($this->getAmount()->getMultiplied(.01), static::getBaseUnit());
				break;
		}
	}

	public function getInUnit(string $unit): Length
	{
		switch (mb_strtolower($unit)) {
			case mb_strtolower(static::getBaseUnit()):
				return $this->getInBaseUnit();
				break;
			case 'cm':
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(100), 'cm');
				break;
		}
	}
}
