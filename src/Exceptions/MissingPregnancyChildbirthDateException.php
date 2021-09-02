<?php

namespace Fatty\Exceptions;

class MissingPregnancyChildbirthDateException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící datum narození dítěte.";
	}
}
