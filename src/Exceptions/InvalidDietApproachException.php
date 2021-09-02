<?php

namespace Fatty\Exceptions;

class InvalidDietApproachException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatný výživový směr.";
	}
}
