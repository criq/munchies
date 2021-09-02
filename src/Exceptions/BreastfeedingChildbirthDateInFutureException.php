<?php

namespace Fatty\Exceptions;

class BreastfeedingChildbirthDateInFutureException extends FattyException
{
	public function __construct()
	{
		$this->message = "Datum narození dítěte je v budoucnosti.";
	}
}
