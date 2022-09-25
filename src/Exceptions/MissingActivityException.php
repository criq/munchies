<?php

namespace Fatty\Exceptions;

class MissingActivityException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící úroveň aktivity.";
		$this->paramKeys = ['activity'];
	}
}
