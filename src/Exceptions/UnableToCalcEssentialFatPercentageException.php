<?php

namespace Fatty\Exceptions;

class UnableToCalcEssentialFatPercentageException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybí údaje k výpočtu procenta esenciálního tělesného tuku.";
	}
}
