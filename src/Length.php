<?php

namespace Fatty;

class Length extends AmountWithUnit
{
	public function __construct(Amount $amount, string $unit = 'cm')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInCm() : Length
	{
		switch ($this->getUnit()) {
			case 'cm':
				return new static($this->getAmount(), 'cm');
				break;
			case 'm':
				return new static($this->getAmount()->getMultiplied(.01), 'cm');
				break;
		}
	}

	public function getInM() : Length
	{
		switch ($this->getUnit()) {
			case 'cm':
				return new static($this->getAmount()->getMultiplied(.01), 'm');
				break;
			case 'm':
				return new static($this->getAmount(), 'm');
				break;
		}
	}
}
