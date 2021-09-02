<?php

namespace Fatty\Exceptions;

class UnableToCalcOptimalFatPercentageException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybí údaje k výpočtu procenta optimálního tělesného tuku.";
	}
}
