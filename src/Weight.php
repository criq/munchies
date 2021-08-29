<?php

namespace Fatty;

class Weight extends AmountWithUnit
{
	public function __construct($amount, $unit = 'kg')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInKg()
	{
		switch ($this->getUnit()) {
			case 'kg':
				return new static($this->getAmount(), 'kg');
				break;
			case 'g':
				return new static($this->getAmount() * .001, 'kg');
				break;
		}
	}

	public function getInG()
	{
		switch ($this->getUnit()) {
			case 'kg':
				return new static($this->getAmount() * 1000, 'g');
				break;
			case 'g':
				return new static($this->getAmount(), 'g');
				break;
		}
	}
}
