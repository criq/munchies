<?php

namespace Fatty\Exceptions;

class InvalidHipsException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný obvod boků.";
		$this->names = ['hips'];
	}
}
