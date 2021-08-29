<?php

namespace Fatty;

class Percentage extends AmountWithUnit
{
	public function __construct(Amount $amount, string $unit = 'percentage')
	{
		return parent::__construct($amount, $unit);
	}

	public function __toString() : string
	{
		return \Katu\Utils\Formatter::getLocalPercent(\Katu\Utils\Formatter::getPreferredLocale(), $this->getAmount()->getValue());
	}

	public function getAsPercentage() : float
	{
		return $this->getAmount()->getValue();
	}
}
