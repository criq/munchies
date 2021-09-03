<?php

namespace Fatty;

class Duration extends AmountWithUnit
{
	public function __construct(Amount $amount, $unit = 'days')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Duration
	{
		switch ($this->getUnit()) {
			case 'days':
				return clone $this;
				break;
			case 'weeks':
				return new static($this->getAmount()->getMultiplied(7), 'weeks');
				break;
		}
	}

	public function getInUnit(string $unit): AmountWithUnit
	{
		switch ($unit) {
			case 'days':
				return $this->getInBaseUnit();
				break;
			case 'weeks':
				return new static($this->getAmount()->getMultiplied(1/7), 'weeks');
				break;
		}
	}
}
