<?php

namespace Fatty;

class Weight extends AmountWithUnit
{
	public function __construct(Amount $amount, string $unit = 'kg')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInKg() : Weight
	{
		switch ($this->getUnit()) {
			case 'kg':
				return new static($this->getAmount(), 'kg');
				break;
			case 'g':
				return new static($this->getAmount()->getMultiplied(.001), 'kg');
				break;
		}
	}

	public function getInG() : Weight
	{
		switch ($this->getUnit()) {
			case 'kg':
				return new static($this->getAmount()->getMultiplied(1000), 'g');
				break;
			case 'g':
				return new static($this->getAmount(), 'g');
				break;
		}
	}
}
