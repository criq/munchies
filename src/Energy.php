<?php

namespace Fatty;

class Energy extends AmountWithUnit
{
	const KCAL_TO_KJ_RATIO = 4.128;

	public function __construct(Amount $amount, string $unit = 'kJ')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInUnit($unit): Energy
	{
		$function = "getIn" . $unit;

		return $this->$function();
	}

	public function getInKJ(): Energy
	{
		switch ($this->getUnit()) {
			case 'kJ':
				return new static($this->getAmount(), 'kJ');
				break;
			case 'kCal':
				return new static(new Amount($this->getAmount()->getValue() * static::KCAL_TO_KJ_RATIO), 'kJ');
				break;
		}
	}

	public function getInKCal(): Energy
	{
		switch ($this->getUnit()) {
			case 'kJ':
				return new static(new Amount($this->getAmount()->getValue() / static::KCAL_TO_KJ_RATIO), 'kCal');
				break;
			case 'kCal':
				return new static($this->getAmount(), 'kCal');
				break;
		}
	}
}
