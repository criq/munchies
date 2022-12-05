<?php

namespace Fatty;

abstract class Nutrient extends Weight
{
	const KJ_IN_G = null;

	public function __construct(Amount $amount, string $unit = "g")
	{
		return parent::__construct(new Amount(max($amount->getValue(), 0)), $unit);
	}

	public static function createFromEnergy(Energy $energy): Nutrient
	{
		return new static(new Amount($energy->getInUnit("kJ")->getAmount()->getValue() / static::KJ_IN_G), "g");
	}

	public function getEnergy(): Energy
	{
		return new Energy(new Amount($this->getInUnit("g")->getAmount()->getValue() * static::KJ_IN_G), "kJ");
	}
}
