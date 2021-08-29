<?php

namespace Fatty;

class Length extends AmountWithUnit
{
	public function __construct($amount, $unit = 'cm')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInCm()
	{
		switch ($this->getUnit()) {
			case 'cm':
				return new static($this->getAmount(), 'cm');
				break;
			case 'm':
				return new static($this->getAmount() * .01, 'cm');
				break;
		}
	}

	public function getInM()
	{
		switch ($this->getUnit()) {
			case 'cm':
				return new static($this->getAmount() / 100, 'm');
				break;
			case 'm':
				return new static($this->getAmount(), 'm');
				break;
		}
	}
}
