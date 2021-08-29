<?php

namespace Fatty;

abstract class Nutrient extends Weight
{
	const KJ_IN_G = null;

	public function __construct($amount, $unit)
	{
		return parent::__construct(max($amount, 0), $unit);
	}

	public static function createFromEnergy($energy)
	{
		return new static($energy->getInKJ()->getAmount() / static::KJ_IN_G, 'g');
	}

	public function getEnergy()
	{
		return new Energy($this->getInG()->getAmount() * static::KJ_IN_G, 'kJ');
	}
}
