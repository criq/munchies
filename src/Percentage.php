<?php

namespace Fatty;

class Percentage extends Amount
{
	public function __toString(): string
	{
		return $this->getFormatted();
	}

	public static function createFromPercent(string $value): ?Amount
	{
		try {
			return new static(parent::createFromString($value)->getValue() * .01);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getFormatted(?Locale $locale = null): string
	{
		$locale = $locale ?: Locale::getDefault();

		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::PERCENT);
		$numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);

		return $numberFormatter->format($this->getValue());
	}
}
