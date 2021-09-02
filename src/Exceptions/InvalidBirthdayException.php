<?php

namespace Fatty\Exceptions;

class InvalidBirthdayException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné datum narození.";
		$this->names = ['birthday'];
	}
}
