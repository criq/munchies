<?php

namespace Fatty\Exceptions;

class InvalidWaistException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný obvod pasu.";
		$this->paramKeys = ['proportions_waist'];
	}
}
