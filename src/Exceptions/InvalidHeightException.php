<?php

namespace Fatty\Exceptions;

class InvalidHeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná výška.";
		$this->names = ['height'];
	}
}
