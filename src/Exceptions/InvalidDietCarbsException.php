<?php

namespace Fatty\Exceptions;

class InvalidDietCarbsException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné množství sacharidů.";
	}
}
