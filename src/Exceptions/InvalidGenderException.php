<?php

namespace Fatty\Exceptions;

class InvalidGenderException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné pohlaví.";
		$this->names = ['gender'];
	}
}
