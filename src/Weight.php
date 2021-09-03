<?php

namespace Fatty;

class Weight extends AmountWithUnit
{
	const BASE_UNIT = 'g';

	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Weight
	{
		switch (mb_strtolower($this->getUnit())) {
			case mb_strtolower(static::getBaseUnit()):
				return clone $this;
				break;
			case 'kg':
				return new static($this->getAmount()->getMultiplied(1000), static::getBaseUnit());
				break;
		}
	}

	public function getInUnit(string $unit): Weight
	{
		switch (mb_strtolower($unit)) {
			case mb_strtolower(static::getBaseUnit()):
				return $this->getInBaseUnit();
				break;
			case 'kg':
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(.001), 'kg');
				break;
		}
	}
}
