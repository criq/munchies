<?php

namespace Fatty;

class Length extends AmountWithUnit
{
	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Length
	{
		switch ($this->getUnit()) {
			case 'm':
				return clone $this;
				break;
			case 'cm':
				return new static($this->getAmount()->getMultiplied(.01), 'm');
				break;

		}
	}

	public function getInUnit(string $unit): Length
	{
		switch ($unit) {
			case 'm':
				return $this->getInBaseUnit();
				break;
			case 'cm':
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(100), 'cm');
				break;
		}
	}
}
