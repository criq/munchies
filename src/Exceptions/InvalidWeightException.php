<?php

namespace Fatty\Exceptions;

class InvalidWeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná hmotnost.";
		$this->names = ['weight'];
	}
}
