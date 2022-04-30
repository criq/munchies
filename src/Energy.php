<?php

namespace Fatty;

class Energy extends Quantity
{
	const BASE_UNIT = "J";
	const CAL_TO_J_RATIO = 4.128;

	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Energy
	{
		switch (mb_strtoupper($this->getUnit())) {
			case "J":
				return clone $this;
				break;
			case "KJ":
				return new static($this->getAmount()->getMultiplied(1000), static::getBaseUnit());
				break;
			case "CAL":
				return new static($this->getAmount()->getMultiplied(static::CAL_TO_J_RATIO), static::getBaseUnit());
				break;
			case "KCAL":
				return new static($this->getAmount()->getMultiplied(static::CAL_TO_J_RATIO * 1000), static::getBaseUnit());
				break;
		}
	}

	public function getInUnit(string $unit): Energy
	{
		switch (mb_strtoupper($unit)) {
			case "J":
				return $this->getInBaseUnit();
				break;
			case "KJ":
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(.001), "kJ");
				break;
			case "CAL":
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(1 / static::CAL_TO_J_RATIO), "cal");
				break;
			case "KCAL":
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied((1 / static::CAL_TO_J_RATIO) * .001), "kcal");
				break;
		}
	}
}
