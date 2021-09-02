<?php

namespace Fatty;

use Fatty\Exceptions\InvalidAmountException;

class Amount
{
	protected $value;

	public function __construct(float $value)
	{
		$this->value = $value;
	}

	public function __toString() : string
	{
		return \Katu\Utils\Formatter::getLocalReadableNumber(\Katu\Utils\Formatter::getPreferredLocale(), $this->getValue());
	}

	public static function createFromString(string $value) : ?Amount
	{
		try {
			$value = trim($value);
			if (!preg_match('/^\-?[0-9]+([\,\.][0-9]+)?$/', $value)) {
				throw new InvalidAmountException;
			}

			return new static((new \Katu\Types\TString(trim($value)))->getAsFloat());
		} catch (\Throwable $e) {
			return null;
		}

		return null;
	}

	public function getValue() : ?float
	{
		return (float)$this->value;
	}

	public function getMultiplied(float $value) : Amount
	{
		return new static($this->getValue() * $value);
	}

	public function getFormatted() : string
	{
		return (string)round($this->getValue(), 1);
	}
}
