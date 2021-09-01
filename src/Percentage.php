<?php

namespace Fatty;

class Percentage extends Amount
{
	public function __toString() : string
	{
		return \Katu\Utils\Formatter::getLocalPercent(\Katu\Utils\Formatter::getPreferredLocale(), $this->getValue());
	}

	public static function createFromPercent(string $value) : ?Amount
	{
		try {
			return new static(parent::createFromString($value)->getValue() * .01);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getAsPercentage() : float
	{
		return $this->getValue();
	}

	public function getArray()
	{
		return [];
	}
}
