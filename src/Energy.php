<?php

namespace Fatty;

class Energy extends AmountWithUnit
{
	const KCAL_TO_KJ_RATIO = 4.128;

	public function __construct($amount, $unit = 'kJ')
	{
		return parent::__construct($amount, $unit);
	}

	public function getInUnit($unit)
	{
		$function = "getIn" . $unit;

		return $this->$function();
	}

	public function getInKJ()
	{
		switch ($this->getUnit()) {
			case 'kJ':
				return new static($this->getAmount(), 'kJ');
				break;
			case 'kCal':
				return new static($this->getAmount() * static::KCAL_TO_KJ_RATIO, 'kJ');
				break;
		}
	}

	public function getInKCal()
	{
		switch ($this->getUnit()) {
			case 'kJ':
				return new static($this->amount / static::KCAL_TO_KJ_RATIO, 'kCal');
				break;
			case 'kCal':
				return new static($this->amount, 'kCal');
				break;
		}
	}
}
