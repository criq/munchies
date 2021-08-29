<?php

namespace Fatty;

class Weight extends AmountWithUnit
{
	public function __construct(float $amount, string $unit = 'kg')
	{
		return parent::__construct($amount, $unit);
	}

	public static function createFromString(string $value) : ?Weight
	{
		try {
			$amount = (new \Katu\Types\TString($value))->getAsFloat();
			if ($amount) {
				return new static($amount);
			}

			return null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getInKg() : Weight
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

	public function getInG() : Weight
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
