<?php

namespace Fatty\Exceptions;

class InvalidNeckException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný obvod krku.";
		$this->names = ['neck'];
	}
}
