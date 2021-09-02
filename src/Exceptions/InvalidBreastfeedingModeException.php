<?php

namespace Fatty\Exceptions;

class InvalidBreastfeedingModeException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný způsob kojení.";
	}
}
