<?php

namespace Fatty;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class Weight extends Quantity
{
	const BASE_UNIT = "g";

	public function __construct(Amount $amount, string $unit)
	{
		return parent::__construct($amount, $unit);
	}

	public static function validateWeight(Param $weight): Validation
	{
		$output = Weight::createFromString($weight, "kg");
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid weight."))->addParam($weight));
		} else {
			return (new Validation)->addParam($weight->setOutput($output));
		}
	}

	public function getInBaseUnit(): Weight
	{
		switch (mb_strtolower($this->getUnit())) {
			case mb_strtolower(static::getBaseUnit()):
				return clone $this;
				break;
			case "kg":
				return new static($this->getAmount()->getMultiplied(1000), static::getBaseUnit());
				break;
		}
	}

	public function getInUnit(string $unit): Weight
	{
		switch (mb_strtolower($unit)) {
			case mb_strtolower(static::getBaseUnit()):
				return $this->getInBaseUnit();
				break;
			case "kg":
				return new static($this->getInBaseUnit()->getAmount()->getMultiplied(.001), "kg");
				break;
		}
	}
}
