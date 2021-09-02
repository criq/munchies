<?php

namespace Fatty\Exceptions;

class InvalidAmountException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné množství.";
	}
}
