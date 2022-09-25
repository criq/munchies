<?php

namespace Fatty\Exceptions;

class InvalidUnitsException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné jednotky.";
		$this->paramKeys = ['units'];
	}
}
