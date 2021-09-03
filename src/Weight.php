<?php

namespace Fatty;

class Weight extends AmountWithUnit
{
	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Weight
	{
		switch ($this->getUnit()) {
			case 'g':
				return clone $this;
				break;
			case 'kg':
				return new static($this->getAmount()->getMultiplied(1000), 'g');
				break;
		}
	}

	public function getInUnit(string $unit): Weight
	{
		switch ($unit) {
			case 'g':
				return $this->getInBaseUnit();
				break;
			case 'kg':
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(.001), 'kg');
				break;
		}
	}
}
