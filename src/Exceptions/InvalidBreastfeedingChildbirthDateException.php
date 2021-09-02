<?php

namespace Fatty\Exceptions;

class InvalidBreastfeedingChildbirthDateException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné datum narození dítěte.";
	}
}
