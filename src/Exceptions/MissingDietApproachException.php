<?php

namespace Fatty\Exceptions;

class MissingDietApproachException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící výživový směr.";
		$this->paramKeys = ['diet_approach'];
	}
}
