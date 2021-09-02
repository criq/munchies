<?php

namespace Fatty\Exceptions;

class PregnancyChildbirthDateInPastException extends FattyException
{
	public function __construct()
	{
		$this->message = "Očekávané datum porodu je v minulosti.";
	}
}
