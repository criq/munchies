<?php

namespace Fatty\SportDurations;

class Anaerobic extends \Fatty\SportDuration
{
	const CODE = "ANAEROBIC";
	const QUOTIENT = 1;

	public function getSportProteinCoefficientKey(): string
	{
		if ($this->getAmount()->getValue() < 120) {
			return "ANAEROBIC_SHORT";
		}

		return $this->getCode();
	}
}
