<?php

namespace Fatty\Exceptions;

class MissingWeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící hmotnost.";
		$this->paramKeys = ['weight'];
	}
}
