<?php

namespace Fatty\Exceptions;

class MissingBreastfeedingChildbirthDateException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící datum narození dítěte.";
	}
}
