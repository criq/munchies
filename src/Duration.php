<?php

namespace Fatty;

class Duration extends Quantity
{
	public function __construct(Amount $amount, $unit = "days")
	{
		return parent::__construct($amount, $unit);
	}

	public function getInBaseUnit(): Duration
	{
		switch (mb_strtolower($this->getUnit())) {
			case "days":
				return clone $this;
				break;
			case "weeks":
				return new static($this->getAmount()->getMultiplied(7), "weeks");
				break;
		}
	}

	public function getInUnit(string $unit): Quantity
	{
		switch (mb_strtolower($unit)) {
			case "days":
				return $this->getInBaseUnit();
				break;
			case "weeks":
				return new static($this->getAmount()->getMultiplied(1 / 7), "weeks");
				break;
		}
	}

	public function getFormatted(?Locale $locale = null): string
	{
		switch (mb_strtolower($this->getUnit())) {
			case "days":
				return "{$this->getAmount()->getValue()} dní";
				break;
			case "weeks":
				return "{$this->getAmount()->getValue()} týdnů";
				break;
		}
	}
}
