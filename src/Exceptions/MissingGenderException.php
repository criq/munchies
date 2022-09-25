<?php

namespace Fatty\Exceptions;

class MissingGenderException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící pohlaví.";
		$this->paramKeys = ['gender'];
	}
}
