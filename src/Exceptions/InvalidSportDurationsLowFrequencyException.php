<?php

namespace Fatty\Exceptions;

class InvalidSportDurationsLowFrequencyException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná doba cvičení nízkofrekvenčních sportů.";
	}
}
