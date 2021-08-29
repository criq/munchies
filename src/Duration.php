<?php

namespace Fatty;

class Duration extends AmountWithUnit
{
	public function __construct(Amount $amount, $unit = 'days')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInWeeks()
	{
		switch ($this->getUnit()) {
			case 'days':
				return new static($this->getAmount()->getMultiplied(1/7), 'weeks');
			break;
			case 'weeks':
				return new static($this->getAmount(), 'weeks');
			break;
		}
	}
}
