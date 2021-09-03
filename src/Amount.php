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

	public function __toString(): string
	{
		return $this->getFormatted();
	}

	public static function createFromString(string $value): ?Amount
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

	public function getValue(): ?float
	{
		return (float)$this->value;
	}

	public function getMultiplied(float $value): Amount
	{
		return new static($this->getValue() * $value);
	}

	public function getFormatted(?Locale $locale = null): string
	{
		$locale = $locale ?: Locale::getDefault();

		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);

		return $numberFormatter->format($this->getValue());
	}
}
