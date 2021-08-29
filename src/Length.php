<?php

namespace Fatty;

class Length extends AmountWithUnit
{
	public function __construct($amount, $unit = 'cm')
	{
		return parent::__construct($amount, $unit);
	}

	public static function createFromString(string $value) : ?Length
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

	public function getInCm() : Length
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

	public function getInM() : Length
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
