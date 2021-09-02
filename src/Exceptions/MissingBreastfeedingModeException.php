<?php

namespace Fatty\Exceptions;

class MissingBreastfeedingModeException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící způsob kojení.";
	}
}
