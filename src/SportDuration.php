<?php

namespace Fatty;

abstract class SportDuration extends Duration
{
	const BASE_UNIT = "minutesPerWeek";
	const CODE = "";
	const QUOTIENT = null;

	public function __construct(Amount $amount, string $unit = "minutesPerWeek")
	{
		return parent::__construct($amount, $unit);
	}

	public static function getCode(): string
	{
		return static::CODE;
	}

	public function getActivity(): Activity
	{
		return new Activity($this->getAmount()->getValue() * .001 * static::QUOTIENT);
	}

	public function getInBaseUnit(): Duration
	{
		return clone $this;
	}

	public function getInUnit(string $unit): AmountWithUnit
	{
		return $this->getInBaseUnit();
	}

	public function getFormatted(?Locale $locale = null): string
	{
		return "{$this->getAmount()->getValue()} minut týdně";
	}
}
