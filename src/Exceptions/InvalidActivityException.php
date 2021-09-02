<?php

namespace Fatty\Exceptions;

class InvalidActivityException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná úroveň aktivity.";
	}
}
