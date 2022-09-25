<?php

namespace Fatty\Exceptions;

class MissingBirthdayException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící datum narození.";
		$this->names = ["birthday"];
	}
}
